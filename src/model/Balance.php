<?php

namespace mon\assets\model;

use Exception;
use mon\env\Config;
use mon\assets\Util;
use mon\assets\AssetException;
use mon\assets\validate\Assets;

/**
 * 用户资产模型
 *
 * @version v1.0.0
 */
class Balance extends Comm
{
    /**
     * 单例实现
     *
     * @var Balance
     */
    protected static $instance;

    /**
     * 新增自动写入字段
     *
     * @var array
     */
    protected $insert = ['create_time', 'update_time'];

    /**
     * 更新自动写入字段
     *
     * @var array
     */
    protected $update = ['update_time'];

    /**
     * 验证器
     *
     * @var Assets
     */
    protected $validate;

    /**
     * 获取单例
     *
     * @return Balance
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
        $this->table = Config::instance()->get('mon_assets.system.assets.name', 'asset_balance');
        $this->tableCount = Config::instance()->get('mon_assets.system.assets.total', 0);
        $this->validate = new Assets;
    }

    /**
     * 查询用户资产
     *
     * @param  int    $uid   用户ID
     * @param  array  $names 查询资产列表，空数组则查询全部
     * @return array 资产信息
     */
    public function infoAction(array $option)
    {
        // 获取数据
        $data = $this->info($option);
        // 解析数据
        $names = isset($option['names']) ? $option['names'] : [];
        $info = [];
        if (!empty($names)) {
            foreach ($data as $item) {
                $info[$item['name']] = [
                    'available' => $item['available'],
                    'freeze'    => $item['freeze'],
                ];
            }
            foreach ($names as $key) {
                if (!isset($info[$key])) {
                    $info[$key] = [
                        'available' => 0,
                        'freeze'    => 0,
                    ];
                }
            }
        } else {
            // 获取所有资产类型
            $types = Config::instance()->get('mon_assets.balance', []);
            $keys = array_keys($types);
            foreach ($data as $item) {
                if (in_array($item['name'], $keys)) {
                    $info[$item['name']] = [
                        'available' => $item['available'],
                        'freeze'    => $item['freeze'],
                    ];
                }
            }
            foreach ($keys as $key) {
                if (!isset($info[$key])) {
                    $info[$key] = [
                        'available' => 0,
                        'freeze'    => 0,
                    ];
                }
            }
        }

        return $info;
    }

    /**
     * 更新用户资产
     *
     * @param  array  $option 请求参数
     * @return array
     */
    public function updateAction(array $option)
    {
        if (!isset($option['type']) || !in_array($option['type'], [1, 2])) {
            throw new AssetException('请输入正确的充值扣减类型，1充值2扣减', 107);
        }
        $type = $option['type'];

        $this->startTrans();
        try {
            if ($type == 1) {
                // 充值
                $data = $this->charge($option);
            } else {
                // 扣减
                $data = $this->deduction($option);
            }

            $usable = $option['usable'];
            $source = isset($option['source']) && is_string($option['source']) ? $option['source'] : '';

            // 记录日志
            $saveLog = Log::instance()->record([
                'uid'                => $data['uid'],
                'from'               => 0,
                'type'               => $type,
                'name'               => $data['name'],
                'source'             => $source,
                'available_before'   => $data['available_before'],
                'available_num'      => $usable == 1 ? $data['amount'] : 0,
                'available_after'    => $data['available_after'],
                'freeze_before'      => $data['freeze_before'],
                'freeze_num'         => $usable == 0 ? $data['amount'] : 0,
                'freeze_after'       => $data['freeze_after'],
            ]);

            // 提交，返回更新后的用户资产
            $this->commit();
            return [
                'uid'       => $data['uid'],
                'name'      => $data['name'],
                'available' => $data['available_after'],
                'freeze'    => $data['freeze_after']
            ];
        } catch (AssetException $e) {
            $this->rollback();
            throw new AssetException($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            $this->rollback();
            Util::ossLog(__FILE__, __LINE__, 'update user assets exception, type => ' . $type . ', file => ' . $e->getFile() .
                ', line => ' . $e->getLine() . ', Message => ' . $e->getMessage(), 'Exception');

            throw new AssetException('更新用户资产异常', 106);
        }
    }

    /**
     * 用户转换资产
     *
     * @param  array  $option [description]
     * @return array
     */
    public function shiftAction(array $option)
    {
        $this->startTrans();
        try {
            $data = $this->shift($option);

            $usable = $option['usable'];
            $type = $usable == 1 ? 4 : 3;
            $source = isset($option['source']) && is_string($option['source']) ? $option['source'] : '';
            // 记录日志
            $saveLog = Log::instance()->record([
                'uid'                => $data['uid'],
                'from'               => 0,
                'type'               => $type,
                'name'               => $data['name'],
                'source'             => $source,
                'available_before'   => $data['available_before'],
                'available_num'      => $data['amount'],
                'available_after'    => $data['available_after'],
                'freeze_before'      => $data['freeze_before'],
                'freeze_num'         => $data['amount'],
                'freeze_after'       => $data['freeze_after'],
            ]);

            $this->commit();
            return [
                'uid'       => $data['uid'],
                'name'      => $data['name'],
                'amount'    => $data['amount'],
                'available' => $data['available_after'],
                'freeze'    => $data['freeze_after']
            ];
        } catch (AssetException $e) {
            $this->rollback();
            throw new AssetException($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            $this->rollback();
            Util::ossLog(__FILE__, __LINE__, 'shift user assets exception, type => ' . $type . ', file => ' . $e->getFile() .
                ', line => ' . $e->getLine() . ', Message => ' . $e->getMessage(), 'Exception');

            throw new AssetException('更新用户资产异常', 106);
        }
    }


    /**
     * 查询资产
     *
     * @param  array  $names   资产名称列表
     * @param  int    $uid     用户ID
     * @return array
     */
    public function info(array $option)
    {
        $check = $this->validate->data($option)->scope('queryBalance')->check();
        if ($check !== true) {
            throw new AssetException($check, 101);
        }
        $uid = $option['uid'];
        $names = isset($option['names']) ? $option['names'] : [];
        $query = $this->table($this->getTableName($uid))->where('uid', $uid)->field('name, available, freeze, status, create_time, update_time');
        if (!empty($names)) {
            // in查询
            $data = $query->whereIn('name', $names)->select();
        } else {
            $data = $query->select();
        }
        Util::ossLog(__FILE__, __LINE__, 'query user assets info => ' . $this->getLastSql(), 'SQL');
        return $data;
    }

    /**
     * 充值用户资产
     *
     * @param  int    $name    资产名称
     * @param  int    $uid     用户ID
     * @param  float  $amount  充值数量
     * @param  int    $usable  1: 操作可用, 0: 操作冻结
     * @return array 成功返回更新后的资产
     */
    public function charge(array $option)
    {
        $check = $this->validate->data($option)->scope('chargeBalance')->check();
        if ($check !== true) {
            throw new AssetException($check, 101);
        }
        $uid = $option['uid'];
        $name = $option['name'];
        $amount = $option['amount'];
        $usable = $option['usable'];

        // 获取判断用户是否已有指定资产类型
        $info = $this->table($this->getTableName($uid))->where('uid', $uid)->where('name', $name)->find();
        Util::ossLog(__FILE__, __LINE__, 'query user assets info => ' . $this->getLastSql(), 'SQL');
        // 存在资产，更新
        if ($info) {
            $field = $usable == 1 ? 'available' : 'freeze';
            $update = [];
            $update[$field] = $info[$field] + $amount;
            $where = [
                'uid'   => $uid,
                'name'  => $name,
            ];
            $where[$field] = $info[$field];
            $save = $this->table($this->getTableName($uid))->save($update, $where);
            Util::ossLog(__FILE__, __LINE__, 'charge user assets => ' . $this->getLastSql());
            if (!$save) {
                throw new AssetException('更新用户资产失败', 104);
            }

            return [
                'uid'               => $uid,
                'name'              => $name,
                'amount'            => $amount,
                'available_after'   => $usable == 1 ? $update['available'] : $info['available'],
                'freeze_after'      => $usable != 1 ? $update['freeze'] : $info['freeze'],
                'available_before'  => $info['available'],
                'freeze_before'     => $info['freeze'],
            ];
        }
        // 不存在，新增
        else {
            // 写入的数据
            $info = [
                'uid'       => $uid,
                'name'      => $name,
                'available' => $usable == 1 ? $amount : 0,
                'freeze'    => $usable == 0 ? $amount : 0,
            ];
            $save = $this->table($this->getTableName($uid))->save($info);
            Util::ossLog(__FILE__, __LINE__, 'charge user assets => ' . $this->getLastSql(), 'SQL');
            if (!$save) {
                throw new AssetException('生成用户资产失败', 105);
            }

            return [
                'uid'               => $uid,
                'name'              => $name,
                'amount'            => $amount,
                'available_after'   => $info['available'],
                'freeze_after'      => $info['freeze'],
                'available_before'  => 0,
                'freeze_before'     => 0,
            ];
        }
    }

    /**
     * 扣减用户资产
     *
     * @param  int    $name    资产名称
     * @param  int    $uid     用户ID
     * @param  float  $amount  充值数量
     * @param  int    $usable  1: 操作可用, 0: 操作冻结
     * @return array 成功返回更新后的资产
     */
    public function deduction(array $option)
    {
        $check = $this->validate->data($option)->scope('deductionBalance')->check();
        if ($check !== true) {
            throw new AssetException($check, 101);
        }
        $uid = $option['uid'];
        $name = $option['name'];
        $amount = $option['amount'];
        $usable = $option['usable'];

        // 获取判断用户是否已有指定资产类型
        $info = $this->table($this->getTableName($uid))->where('uid', $uid)->where('name', $name)->find();
        Util::ossLog(__FILE__, __LINE__, 'query user assets info => ' . $this->getLastSql(), 'SQL');
        // 不存在资产，返回false
        if (!$info) {
            throw new AssetException('获取用户资产失败，或用户未有该资产', 102);
        }
        // 判断资产是否足够
        $field = $usable == 1 ? 'available' : 'freeze';
        if ($info[$field] < $amount) {
            throw new AssetException('用户资产不足', 103);
        }

        // 扣减
        $update = [];
        $update[$field] = $info[$field] - $amount;
        $where = [
            'uid'   => $uid,
            'name'  => $name,
        ];
        $where[$field] = $info[$field];
        // 更新资产
        $save = $this->table($this->getTableName($uid))->save($update, $where);
        Util::ossLog(__FILE__, __LINE__, 'deduction user assets => ' . $this->getLastSql());
        if (!$save) {
            throw new AssetException('更新用户资产失败', 104);
        }

        return [
            'uid'               => $uid,
            'name'              => $name,
            'amount'            => $amount,
            'available_after'   => $usable == 1 ? $update['available'] : $info['available'],
            'freeze_after'      => $usable != 1 ? $update['freeze'] : $info['freeze'],
            'available_before'  => $info['available'],
            'freeze_before'     => $info['freeze'],
        ];
    }

    /**
     * 用户资产转换
     *
     * @param  int    $name    资产名称
     * @param  int    $uid     用户ID
     * @param  float  $amount  充值数量
     * @param  int    $usable  1: 冻结转可用, 0: 可用转冻结
     * @return array 成功返回更新后的资产
     */
    public function shift(array $option)
    {
        $check = $this->validate->data($option)->scope('shiftBalance')->check();
        if ($check !== true) {
            throw new AssetException($check, 101);
        }
        $uid = $option['uid'];
        $name = $option['name'];
        $amount = $option['amount'];
        $usable = $option['usable'];

        // 获取判断用户是否已有指定资产类型
        $info = $this->table($this->getTableName($uid))->where('uid', $uid)->where('name', $name)->find();
        Util::ossLog(__FILE__, __LINE__, 'query user assets info => ' . $this->getLastSql(), 'SQL');
        // 不存在资产，返回false
        if (!$info) {
            throw new AssetException('获取用户资产失败，或用户未有该资产', 102);
        }
        // 获取字段
        $charge_field = $usable == 1 ? 'available' : 'freeze';
        $deduction_field = $usable == 0 ? 'available' : 'freeze';
        // 判断资产是否足够
        if ($info[$deduction_field] < $amount) {
            throw new AssetException('用户资产不足', 103);
        }

        // 转换资产
        $update = [];
        $update[$charge_field] = $info[$charge_field] + $amount;
        $update[$deduction_field] = $info[$deduction_field] - $amount;
        $where = [
            'uid'       => $uid,
            'name'      => $name,
            'available' => $info['available'],
            'freeze'    => $info['freeze']
        ];
        // 更新资产
        $save = $this->table($this->getTableName($uid))->save($update, $where);
        Util::ossLog(__FILE__, __LINE__, 'shift user assets => ' . $this->getLastSql());
        if (!$save) {
            throw new AssetException('更新用户资产失败', 104);
        }

        return [
            'uid'               => $uid,
            'name'              => $name,
            'amount'            => $amount,
            'available_after'   => $update['available'],
            'freeze_after'      => $update['freeze'],
            'available_before'  => $info['available'],
            'freeze_before'     => $info['freeze'],
        ];
    }

    /**
     * 自动完成create_time字段
     * 
     * @return int
     */
    protected function setCreateTimeAttr($val)
    {
        return $_SERVER['REQUEST_TIME'];
    }

    /**
     * 自动完成update_time字段
     * 
     * @return int
     */
    protected function setUpdateTimeAttr($val)
    {
        return $_SERVER['REQUEST_TIME'];
    }
}
