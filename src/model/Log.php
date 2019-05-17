<?php
namespace mon\assets\model;

use mon\env\Config;
use mon\assets\Util;
use mon\assets\AssetException;
use mon\assets\validate\Assets;

/**
 * 用户资产模型
 *
 * @version v1.0.0
 */
class Log extends Comm
{
    /**
     * 单例实现
     *
     * @var [type]
     */
    protected static $instance;

    /**
     * type类型说明
     *
     * @var [type]
     */
    protected $typeInfo = [
        1 => '充值',
        2 => '扣减',
        3 => '可用转冻结',
        4 => '冻结转可用',
        5 => '兑换支出',
        6 => '兑换收入',
        7 => '转出',
        8 => '转入'
    ];

    /**
     * 新增自动写入字段
     *
     * @var [type]
     */
    protected $insert = ['create_time'];

    /**
     * 验证器
     *
     * @var [type]
     */
    protected $validate;

    /**
     * 获取单例
     *
     * @return [type] [description]
     */
    public static function instance()
    {
        if(is_null(self::$instance)){
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
        $this->table = Config::instance()->get('mon_assets.system.log.name', 'asset_balance_log');
        $this->tableCount = Config::instance()->get('mon_assets.system.log.total', 0);
        $this->validate = new Assets;
    }

    /**
     * 查询日志流水
     *
     * @param  array  $option [description]
     * @return [type]         [description]
     */
    public function historyAction(array $option)
    {
        $types = Config::instance()->get('mon_assets.balance', []);
        $keys = array_keys($types);
        if(isset($option['name']) && (empty($option['name']) || !is_string($option['name']) || !in_array($option['name'], $keys))){
            throw new AssetException('资产名称为空或资产类型不存在', 202);
        }
        if(isset($option['source']) && (empty($option['source']) || !is_string($option['source']))){
            throw new AssetException('来源不能为空', 203);
        }
        if(isset($option['from']) && (!is_numeric($option['from']) || !is_int($option['from'] + 0))){
            throw new AssetException('来源人ID格式错误', 204);
        }
        if(isset($option['type']) && (!is_numeric($option['type']) || !is_int($option['type'] + 0))){
            throw new AssetException('类型格式错误', 205);
        }
        if(isset($option['start_time']) && (!is_numeric($option['start_time']) || !is_int($option['start_time'] + 0) || $option['start_time'] < 0)){
            throw new AssetException('起始时间格式错误', 206);
        }
        if(isset($option['end_time']) && (!is_numeric($option['end_time']) || !is_int($option['end_time'] + 0) || $option['end_time'] < 0)){
            throw new AssetException('结束时间格式错误', 207);
        }
        return $this->history($option);
    }

    /**
     * 查询日志
     *
     * @param  int    $uid        用户ID
     * @param  string $name       资产名称
     * @param  int    $start_time 开始时间
     * @param  int    $end_time   结束时间
     * @param  int    $offset     偏移数
     * @param  int    $limit      查询记录数
     * @return [type]             [description]
     */
    public function history(array $option)
    {
        // 验证获取参数
        $check = $this->validate->data($option)->scope('queryLog')->check();
        if($check !== true){
            throw new AssetException($check, 201);
        }
        $offset = isset($option['offset']) ? intval($option['offset']) : 0;
        $limit = isset($option['limit']) ? intval($option['limit']) : 10;

        $data = $this->scope('list', $option)->limit($offset, $limit)->select();
        Util::ossLog(__FILE__, __LINE__, 'query user assets log => '.$this->getLastSql(), 'SQL');
        $total = $this->scope('list', $option)->count('id');
        Util::ossLog(__FILE__, __LINE__, 'query user assets log count => '.$this->getLastSql(), 'SQL');

        return [
            'uid'   => $option['uid'],
            'offset'=> $offset,
            'limit' => $limit,
            'data'  => $data,
            'total' => $total
        ];
    }

    /**
     * 构建查询对象
     *
     * @param  [type] $query [description]
     * @param  array  $args  [description]
     * @return [type]        [description]
     */
    protected function scopeList($query, array $args)
    {
        $uid = $args['uid'];
        $cond = $query->table($this->getTableName($uid))->where('uid', $uid)->order('id', 'DESC');
        if(isset($args['name']) && !empty($args['name']) && is_string($args['name'])){
            $cond->where('name', $args['name']);
        }
        if(isset($args['source']) && !empty($args['source']) && is_string($args['source'])){
            $cond->where('source', $args['source']);
        }
        if(isset($args['from']) && is_numeric($args['from']) && is_int($args['from'] + 0)){
            $cond->where('from', $args['from']);
        }
        if(isset($args['type']) && is_numeric($args['type']) && is_int($args['type'] + 0)){
            $cond->where('type', $args['type']);
        }
        if(isset($args['start_time']) && is_numeric($args['start_time']) && is_int($args['start_time'] + 0) && $args['start_time'] > 0){
            $cond->where('create_time', '>=', $args['start_time']);
        }
        if(isset($args['end_time']) && is_numeric($args['end_time']) && is_int($args['end_time'] + 0) && $args['end_time'] > 0){
            $cond->where('create_time', '<=', $args['end_time']);
        }

        return $cond;
    }

    /**
     * 记录日志
     *
     * @param  array  $option [description]
     * @return [type]         [description]
     */
    public function record(array $option)
    {
        // 验证获取参数
        $check = $this->validate->data($option)->scope('record_log')->check();
        if($check !== true){
            throw new AssetException($check, 201);
        }
        $info = [
            'uid'                => $option['uid'],
            'from'               => $option['from'],
            'type'               => $option['type'],
            'name'               => $option['name'],
            'source'             => isset($option['source']) && is_string($option['source']) ? $option['source'] : '',
            'available_before'   => $option['available_before'],
            'available_num'      => $option['available_num'],
            'available_after'    => $option['available_after'],
            'freeze_before'      => $option['freeze_before'],
            'freeze_num'         => $option['freeze_num'],
            'freeze_after'       => $option['freeze_after'],
        ];

        $record = $this->table($this->getTableName($option['uid']))->save($info);
        Util::ossLog(__FILE__, __LINE__, 'record user assets log => '.$this->getLastSql(), 'SQL');
        if(!$record){
            throw new AssetException('记录日志流水失败', 208);
        }

        return true;
    }

    /**
     * 自动完成create_time字段
     * 
     * @param [type] $val 默认值
     * @param array  $row 列值
     */
    protected function setCreateTimeAttr($val)
    {
        return $_SERVER['REQUEST_TIME'];
    }
}