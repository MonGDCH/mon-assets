<?php
namespace mon\assets\model;

use Exception;
use mon\env\Config;
use mon\assets\Util;
use mon\assets\AssetException;
use mon\assets\validate\Assets;

/**
 * 转换模型
 *
 * @version v1.0.0
 */
class Transform extends Comm
{
    /**
     * 验证器
     *
     * @var [type]
     */
    protected $validate;

    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
        $this->validate = new Assets;
    }

    /**
     * 兑换接口, 用户HTTP, 保持接口一致化
     *
     * @param  array  $option [description]
     * @return [type]         [description]
     */
    public function converAction(array $option)
    {
        return $this->conver($option);
    }

    /**
     * 转账接口, 用户HTTP, 保持接口一致化
     *
     * @param  array  $option [description]
     * @return [type]         [description]
     */
    public function transferAction(array $option)
    {
        return $this->transfer($option);
    }

    /**
     * 兑换
     *
     * @param  int    $uid         用户ID
     * @param  int    $from_amount 来源资产扣减数量
     * @param  int    $to_amount   目标资产增加数量
     * @param  int    $from_usable 来源资产类型, 1可用;2冻结
     * @param  int    $to_usable   目标资产类型, 1可用;2冻结
     * @param  string $from_name   来源资产名称
     * @param  string $to_name     目标资产资产名称
     * @return [type] [description]
     */
    public function conver(array $option)
    {
        $check = $this->validate->data($option)->scope('converBalance')->check();
        if($check !== true){
            throw new AssetException($check, 301);
        }
        $uid = $option['uid'];
        $from_name = $option['from_name'];
        $to_name = $option['to_name'];
        $from_amount = $option['from_amount'];
        $to_amount = $option['to_amount'];
        $from_usable = $option['from_usable'];
        $to_usable = $option['to_usable'];
        $source = isset($option['source']) && is_string($option['source']) ? $option['source'] : '';

        // 兑换资产
        $this->startTrans();
        try{
            // 扣减来源资产
            $deduction = Balance::instance()->deduction([
                'uid'   => $uid,
                'name'  => $from_name,
                'amount'=> $from_amount,
                'usable'=> $from_usable,
            ]);

            // 记录扣减来源资产日志
            $saveDeductionLog = Log::instance()->record([
                'uid'                => $deduction['uid'],
                'from'               => 0,
                'type'               => 5,
                'name'               => $deduction['name'],
                'source'             => $source,
                'available_before'   => $deduction['available_before'],
                'available_num'      => $deduction['amount'],
                'available_after'    => $deduction['available_after'],
                'freeze_before'      => $deduction['freeze_before'],
                'freeze_num'         => $deduction['amount'],
                'freeze_after'       => $deduction['freeze_after'],
            ]);

            // 增加目标资产
            $charge = Balance::instance()->charge([
                'uid'   => $uid,
                'name'  => $to_name,
                'amount'=> $to_amount,
                'usable'=> $to_usable,
            ]);

            // 记录增加目标资产日志
            $saveChargeLog = Log::instance()->record([
                'uid'                => $charge['uid'],
                'from'               => 0,
                'type'               => 6,
                'name'               => $charge['name'],
                'source'             => $source,
                'available_before'   => $charge['available_before'],
                'available_num'      => $charge['amount'],
                'available_after'    => $charge['available_after'],
                'freeze_before'      => $charge['freeze_before'],
                'freeze_num'         => $charge['amount'],
                'freeze_after'       => $charge['freeze_after'],
            ]);

            $this->commit();
            $info = [];
            $info[$from_name] = [
                'available' => $deduction['available_after'],
                'freeze'    => $deduction['freeze_after'],
            ];
            $info[$to_name] = [
                'available' => $charge['available_after'],
                'freeze'    => $charge['freeze_after'],
            ];
            return $info;
        }
        catch(AssetException $e){
            $this->rollback();
            throw new AssetException($e->getMessage(), $e->getCode());
        }
        catch(Exception $e){
            $this->rollback();
            Util::ossLog(__FILE__, __LINE__, 'conver user assets exception, file => '.$e->getFile() . 
                                            ', line => '.$e->getLine() . ', Message => '.$e->getMessage(), 'Exception');

            throw new AssetException('用户资产兑换异常', 303);
        }
    }

    /**
     * 转账
     *
     * @param  int    $from_amount 来源用户扣减数量
     * @param  int    $to_amount   目标用户增加数量
     * @param  int    $from_id     来源用户ID
     * @param  int    $to_id       目标用户ID
     * @param  int    $from_usable 来源用户资产类型, 1可用;2冻结
     * @param  int    $to_usable   目标用户资产类型, 1可用;2冻结
     * @param  string $from_name   来源用户资产名称
     * @param  string $to_name     目标用户资产名称
     * @return [type]              [description]
     */
    public function transfer(array $option)
    {
        $check = $this->validate->data($option)->scope('transferBalance')->check();
        if($check !== true){
            throw new AssetException($check, 301);
        }
        $from_id = $option['from_id'];
        $to_id = $option['to_id'];
        $from_name = $option['from_name'];
        $to_name = $option['to_name'];
        $from_amount = $option['from_amount'];
        $to_amount = $option['to_amount'];
        $from_usable = $option['from_usable'];
        $to_usable = $option['to_usable'];
        $source = isset($option['source']) && is_string($option['source']) ? $option['source'] : '';

        // 兑换资产
        $this->startTrans();
        try{
            // 扣减来源资产
            $deduction = Balance::instance()->deduction([
                'uid'   => $from_id,
                'name'  => $from_name,
                'amount'=> $from_amount,
                'usable'=> $from_usable,
            ]);

            // 记录扣减来源资产日志
            $saveDeductionLog = Log::instance()->record([
                'uid'                => $deduction['uid'],
                'from'               => $to_id,
                'type'               => 7,
                'name'               => $deduction['name'],
                'source'             => $source,
                'available_before'   => $deduction['available_before'],
                'available_num'      => $deduction['amount'],
                'available_after'    => $deduction['available_after'],
                'freeze_before'      => $deduction['freeze_before'],
                'freeze_num'         => $deduction['amount'],
                'freeze_after'       => $deduction['freeze_after'],
            ]);

            // 增加目标资产
            $charge = Balance::instance()->charge([
                'uid'   => $to_id,
                'name'  => $to_name,
                'amount'=> $to_amount,
                'usable'=> $to_usable,
            ]);

            // 记录增加目标资产日志
            $saveChargeLog = Log::instance()->record([
                'uid'                => $charge['uid'],
                'from'               => $from_id,
                'type'               => 8,
                'name'               => $charge['name'],
                'source'             => $source,
                'available_before'   => $charge['available_before'],
                'available_num'      => $charge['amount'],
                'available_after'    => $charge['available_after'],
                'freeze_before'      => $charge['freeze_before'],
                'freeze_num'         => $charge['amount'],
                'freeze_after'       => $charge['freeze_after'],
            ]);

            $this->commit();
            $info = [];
            $info['from_user'][$from_name] = [
                'uid'       => $from_id,
                'available' => $deduction['available_after'],
                'freeze'    => $deduction['freeze_after'],
            ];
            $info['to_user'][$to_name] = [
                'uid'       => $to_id,
                'available' => $charge['available_after'],
                'freeze'    => $charge['freeze_after'],
            ];
            return $info;
        }
        catch(AssetException $e){
            $this->rollback();
            throw new AssetException($e->getMessage(), $e->getCode());
        }
        catch(Exception $e){
            $this->rollback();
            Util::ossLog(__FILE__, __LINE__, 'transfer user assets exception, file => '.$e->getFile() . 
                                            ', line => '.$e->getLine() . ', Message => '.$e->getMessage(), 'Exception');
            throw new AssetException('用户资产转账异常', 302);
        }
    }
}