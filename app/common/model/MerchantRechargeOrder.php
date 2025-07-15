<?php


namespace app\common\model;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 商户充值表
 * Class Merchant
 */
class MerchantRechargeOrder extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

   
}