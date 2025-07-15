<?php


namespace app\common\model;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;
use app\model\AdminMember;

/**
 * 商户表资金记录表
 * Class Merchant
 */
class MerchantAccountLog extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    public function merchant()
    {
        return $this->hasOne(Merchant::class, 'id','mch_id');
    }
    public function member()
    {
        return $this->hasOne(AdminMember::class, 'member_id','update_by');
    }
}