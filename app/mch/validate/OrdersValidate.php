<?php

namespace app\mch\validate;

use app\common\validate\BaseValidate;


/**
 * 订单管理验证
 * Class MerchantValidate
 */
class OrdersValidate extends BaseValidate
{
    protected $rule = [
        'channel_id' => 'require|number',
        'pay_type' => 'require|between:1,2',
        'bank_id' => 'require|number',
        'amount' => 'require|float',
        'bank_name' => 'require',
        'user_name' => 'require',
        'bank_num' => 'require',
        'type' => 'require|between:1,2',
        'wallet_address' => 'require',
    ];

    protected $message = [
        'type.require'=> '充值类型不能为空',
        'wallet_address.require'=> '银行名称不能为空',
        'bank_name.require'=> '银行名称不能为空',
        'user_name.require'=> '持卡人名称不能为空',
        'bank_num.require'=> '银行卡号不能为空',
        'amount.require'=> '金额不能为空',
        'bank_num.require'=> '金额不能为空',
        'amount.float'=> '金额是float类型',
        'channel_id.require'=> '商户通道编号不能为空',
        'channel_id.number'=> '商户通道编号是数字',
        'bank_id.require'=> '银行卡ID不能为空',
        'bank_id.number'=> '银行卡ID是数字',
        'pay_type.require' => '支付类型不能为空',
        'pay_type.between' => '支付类型只能是1或2',
    ];
    
    /**
     * @notes 代收
     */
    public function sceneCheckin()
    {
        return $this->only(['channel_id','pay_type','bank_id','amount']);
    }
    /**
     * @notes 代付
     */
    public function sceneCheckout()
    {
        return $this->only(['channel_id','pay_type','bank_name','user_name','bank_num','amount']);
    }
    /**
     * @notes 充值
     */
    public function sceneAddRecharge()
    {
        return $this->only(['type','pay_type','amount']);
    }
    /**
     * @notes 提现
     */
    public function sceneAddWithdraw()
    {
        return $this->only(['type','pay_type','wallet_address','amount']);
    }



}