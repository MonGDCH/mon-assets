<?php
namespace mon\assets\validate;

use mon\env\Config;
use mon\util\Validate;

/**
 * 验证器
 */
class Assets extends Validate
{
    /**
     * 验证规则
     * @var [type]
     */
    public $rule = [
        'uid'           => 'required|int|min:1',
        'name'          => 'required|str|maxLength:18|isname',
        'names'         => 'arr|isnames',
        'amount'        => 'required|int|min:0',
        'usable'        => 'required|in:0,1',

        'from_id'       => 'required|int|min:1',
        'to_id'         => 'required|int|min:1',
        'from_usable'   => 'required|in:0,1',
        'to_usable'     => 'required|in:0,1',
        'from_name'     => 'required|str|maxLength:18|isname',
        'to_name'       => 'required|str|maxLength:18|isname',
        'from_amount'   => 'required|int|min:0',
        'to_amount'     => 'required|int|min:0',

        'type'          => 'required|int|min:0',
        'form'              => 'required|int|min:0',
        'available_before'  => 'required|int|min:0',
        'available_num'     => 'required|int|min:0',
        'available_after'   => 'required|int|min:0',
        'freeze_before'     => 'required|int|min:0',
        'freeze_num'        => 'required|int|min:0',
        'freeze_after'      => 'required|int|min:0',
    ];

    /**
     * 错误提示信息
     * @var [type]
     */
    public $message = [
        'uid'           => '用户ID格式错误',
        'name'          => [
            'required'  => '资产名称必须',
            'str'       => '资产名称格式错误',
            'maxLength' => '资产名称长度不能超过18位',
            'isname'    => '资产名称不支持'
        ],
        'names'         => [
            'arr'       => '资产名称列表格式错误',
            'isnames'   => '资产名称不支持',
        ],
        'amount'        => '数量格式错误',
        'usable'        => 'usable参数错误',
        'type'          => '类型参数错误',
        'from_id'       => '来源用户ID格式错误',
        'to_id'         => '目标用户ID格式错误',
        'from_usable'   => 'from_usable参数错误',
        'to_usable'     => 'to_usable参数错误',
        'from_name'     => [
            'required'  => '来源资产名称必须',
            'str'       => '来源资产名称格式错误',
            'maxLength' => '来源资产名称长度不能超过18位',
            'isname'    => '来源资产名称不支持'
        ],
        'to_name'       => [
            'required'  => '目标资产名称必须',
            'str'       => '目标资产名称格式错误',
            'maxLength' => '目标资产名称长度不能超过18位',
            'isname'    => '目标资产名称不支持'
        ],
        'from_amount'   => '来源数量格式错误',
        'to_amount'     => '目标数量格式错误',

        'form'              => '来源用户ID格式错误',
        'available_before'  => '可用资产变更前数量参数错误',
        'available_num'     => '可用资产变更数量参数错误',
        'available_after'   => '可用资产变更后数量参数错误',
        'freeze_before'     => '冻结资产变更前数量参数错误',
        'freeze_num'        => '冻结资产变更数量参数错误',
        'freeze_after'      => '冻结资产变更后数量参数错误',
    ];

    /**
     * 验证场景
     * @var [type]
     */
    public $scope = [
        'queryLog'          => ['uid'],
        'queryBalance'      => ['uid', 'names'],
        'chargeBalance'     => ['uid', 'name', 'amount', 'usable'],
        'deductionBalance'  => ['uid', 'name', 'amount', 'usable'],
        'shiftBalance'      => ['uid', 'name', 'amount', 'usable'],
        'converBalance'     => ['uid', 'from_usable', 'to_usable', 'from_name', 'to_name', 'from_amount', 'to_amount'],
        'transferBalance'   => ['from_id', 'to_id', 'from_usable', 'to_usable', 'from_name', 'to_name', 'from_amount', 'to_amount'],
        'record_log'        => ['uid', 'type', 'from', 'name', 'available_before', 'available_num', 'available_after', 
                                'freeze_before', 'freeze_num', 'freeze_after']
    ];

    /**
     * 验证资产类型是否有效
     *
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function isname($value)
    {
        $names = Config::instance()->get('mon_assets.balance', []);
        $data = array_keys($names);

        return in_array($value, $data);
    }

    /**
     * 验证资产类型列表是否合法
     *
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function isnames($value)
    {
        foreach($value as $item)
        {
            if(!$this->isname($item)){
                return false;
            }
        }

        return true;
    }
}