<?php


namespace app\common\model;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;
use app\common\service\FileService;
use app\model\AdminMember;

/**
 * 代付订单
 * Class PayoutOrder
 */
class PayoutOrder extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    
    /**
     * @notes 图片地址拼接域名
     * @param $value
     * @return string
     */
    public function getImageAttr($value)
    {
        // return trim($value) ? FileService::getFileUrl($value) : '';
    }
        public function channel()
    {
        return $this->hasOne(Channel::class, 'id','channel_id');
    }
    public function merchant()
    {
        return $this->hasOne(Merchant::class, 'id','mch_id');
    }
    public function member()
    {
        return $this->hasOne(AdminMember::class, 'member_id','update_by');
    }
    
    /**
     * @notes 清除图片地址
     * @param $value
     * @param $data
     * @return array|string|string[]
     */
    public function setImageAttr($value)
    {
        // return trim($value) ? FileService::setFileUrl($value) : '';
    }
   
}