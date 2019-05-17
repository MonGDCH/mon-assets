<?php
namespace mon\assets\model;

use mon\orm\Model;
use mon\env\Config;
use mon\assets\Asset;
use mon\assets\AssetException;

/**
 * 自定义模型基类
 *
 * @version v1.0.0
 */
class Comm extends Model
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        if(!Asset::instance()->isInit()){
            throw new AssetException('system not init', -2);
        }
        $this->config = Config::instance()->get('mon_assets.database', []);
    }

    /**
     * 获取表名称
     *
     * @param  int    $uid 用户ID
     * @return [type]      [description]
     */
    public function getTableName(int $uid)
    {
        if($this->tableCount <= 1){
            return $this->table;
        }

        $num = $uid % $this->tableCount;
        return $this->table . '_' . $num;
    }
}