<?php

namespace app\admin\validate\order;

use app\common\validate\BaseValidate;


/**
 * 商户管理验证
 * Class MerchantValidate
 */
class PayinValidate extends BaseValidate
{
    protected $rule = [
        'order_sn' => 'require',
        'status' => 'require|in:2,3',
    ];

    protected $message = [
        'order_sn.require'=> '订单号不能为空',
        'status.require' => '状态不能为空',
        'status.in' => '状态只能是2或3',
    ];
    
    /**
     * @notes 回调
     */
    public function sceneCallback()
    {
        return $this->only(['order_sn','status']);
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
    public function sceneClose()
    {
        return $this->only(['order_sn']);
    }
    public function sceneNotifier()
    {
        return $this->only(['order_sn']);
    }




}