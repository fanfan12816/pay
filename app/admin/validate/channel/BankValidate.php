<?php

namespace app\admin\validate\channel;

use app\common\validate\BaseValidate;


/**
 * 商户管理验证
 * Class MerchantValidate
 */
class BankValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'channel_id' => 'require',
        'status' => 'require|in:0,1',
        'pay_type' => 'require|in:0,1',
        'type' => 'require|in:1,2',
    ];

    protected $message = [
        'id.require'=> 'ID不能为空',
        'channel_id.require'=> '通道ID不能为空',
        'status.in'=> '状态只能是0或1',
        'pay_type.in'=> '通道状态只能是0或1',
        'type.in'=> '代收状态只能是1或2',
    ];
    
    /**
     * @notes 新增
     */
    public function sceneAdd()
    {
        return $this->only(['channel_id','status','pay_type','type']);
    }
    /**
     * @notes 修改商户场景
     */
    public function sceneEdit()
    {
        return $this->only(['id','channel_id','status','pay_type','type']);
    }
    /**
     * @notes 修改密码场景
     */
    public function sceneDel()
    {
        return $this->only(['id']);
    }
   

}