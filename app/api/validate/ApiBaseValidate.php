<?php

namespace app\api\validate;

use app\common\validate\BaseValidate;


/**
 * 验证
 * Class MerchantValidate
 */
class ApiBaseValidate extends BaseValidate
{
    protected $rule = [
        'mch_id' => 'require',
        'channel_id' => 'require',
        'mch_sn' => 'require',
        'order_sn' => 'require',
        'notify_url' => 'require',
        'amount' => 'require|float|gt:0',
        'payer_name' => 'require',
        'time' => 'require|number',
        'bank_id' => 'require',
        'bank_num' => 'require',
        'bank_name' => 'require',
        'user_name' => 'require',
        'sign' => 'require',
    ];

    protected $message = [
        'mch_id.require'=> '商户编号不能为空',
        'channel_id.require'=> '通道编号不能为空',
        'mch_sn.require'=> '商户订单号不能为空',
        'order_sn.require'=> '平台订单号不能为空',
        'notify_url.require'=> '商户回调地址不能为空',
        'amount.require'=> '下单金额不能为空',
        'amount.float'=> '金额是浮点数',
        'amount.gt'=> '金额必须大于0',
        'time.number'=> '时间戳是INT类型',
        'payer_name.require'=> '付款人姓名不能为空',
        'sign.require'=> '签名不能为空',
        'bank_name.require'=> '银行名字不能为空',
        'bank_num.require'=> '收款人银行卡号码不能为空',
        'user_name.require'=> '收款人姓名不能为空',
    ];
    
    /**
     * @notes 代收下单
     */
    public function scenePayinTransactions()
    {
        return $this->only(['mch_id','channel_id','mch_sn','notify_url','amount','time','sign']);
    }
     /**
     * @notes 代付批量下单
     */
    public function scenePayoutBulkOrders()
    {
        return $this->only(['mch_id','channel_id','data','time','sign']);
    }
    /**
     * @notes 代付下单
     */
    public function scenePayoutTransactions()
    {
        return $this->only(['mch_id','mch_sn','channel_id','notify_url','amount','bank_name','bank_num','user_name','time','sign']);
    }
    /**
     * @notes 代付支持银行列表
     */
    public function scenePayoutBanklists()
    {
        return $this->only(['mch_id','channel_id','time','sign']);
    }
    /*
     * @notes 代付支持银行列表
     */
    public function sceneQueryMerchant()
    {
        return $this->only(['mch_id','time','sign']);
    }
    /*
     * @notes 代付支持银行列表
     */
    public function sceneQueryinChannel()
    {
        return $this->only(['mch_id','channel_id','time','sign']);
    }
    /*
     * @notes 代付支持银行列表
     */
    public function sceneQueryinPayin()
    {
        return $this->only(['mch_id','channel_id','order_sn','time','sign']);
    }
    /*
     * @notes 代付支持银行列表
     */
    public function sceneQueryinPayOut()
    {
        return $this->only(['mch_id','channel_id','order_sn','time','sign']);
    }

   /**
     * @notes 验证密码不能一样
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     */
    public function edit($value, $rule, $data)
    {
        $password=$data['password']??"";
        $password_new=$data['password_new']??"";
        if ($password === $password_new) {
            return '新密码不能和旧密码一样';
        }
        $len = strlen($value);
        if ($len < 6 || $len > 32) {
            return '密码长度须在6-32位字符';
        }
        return true;
    }


}