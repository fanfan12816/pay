<?php

namespace app\admin\validate\order;

use app\common\validate\BaseValidate;


/**
 * 商户管理验证
 * Class MerchantValidate
 */
class WithdrawValidate extends BaseValidate
{
     protected $rule = [
        'order_sn' => 'require',
        'rate' => 'require',
        'service_charge' => 'require',
        'status' => 'require|in:1,2',
    ];

    protected $message = [
        'rate.require'=> '当前汇率不能为空',
        'service_charge.require'=> '当前汇率不能为空',
        'order_sn.require'=> '订单号不能为空',
        'status.require' => '状态不能为空',
        'status.in' => '状态只能是1或2',
    ];
    
    /**
     * @notes
     */
    public function sceneCheck()
    {
        return $this->only(['order_sn','status','rate','service_charge']);
    }
     public function sceneCheckSuccess()
    {
        return $this->only(['order_sn','status','rate','service_charge']);
    }
    /**
     * @notes 
     */
    public function sceneDel()
    {
        return $this->only(['order_sn']);
    }
    /**
     * @notes 
     */
    public function sceneCheckFail()
    {
        return $this->only(['order_sn','status']);
    }





}