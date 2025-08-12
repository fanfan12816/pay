<?php


namespace app\common\model;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 商户提现表
 * Class Merchant
 */
class MerchantWithdrawOrder extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

   
}