<?php


namespace app\common\model;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 商户通道绑定
 * Class Merchant
 */
class MerchantChannel extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

   
}