<?php


namespace app\common\model;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;
use app\common\service\FileService;
use app\model\AdminMember;
/**
 * 代收订单
 * Class PayinOrder
 */
class PayinOrder extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    
    public function getThemeAttr($value, $data)
    {
        return Language::where(['mch_id' => $data["mch_id"],"channel_id"=>$data["channel_id"]])->value('theme');
    }
    public function getChannelTitleAttr($value, $data)
    {
        return Channel::where(["id"=>$data["channel_id"]])->value('name');
    }
    public function getMchNickNameAttr($value, $data)
    {
        return Merchant::where(["id"=>$data["mch_id"]])->value('nick_name');
    }
    public function channel()
    {
        return $this->hasOne(Channel::class, 'id','channel_id');
    }
    public function bank()
    {
        return $this->hasOne(ChannelBank::class, 'id','bank_id')->bind([
            "bank_name",
            "user_name",
            "bank_num",
            "iban",
        ]);
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
     * @notes 图片地址拼接域名
     * @param $value
     * @return string
     */
    public function getImageAttr($value)
    {
        if($value){
            return trim($value) ? FileService::getFileUrl($value) : '';
        }else{
            return "";
        }
    }
    
    /**
     * @notes 清除图片地址
     * @param $value
     * @param $data
     * @return array|string|string[]
     */
    public function setImageAttr($value)
    {
        if($value){
            return trim($value) ? FileService::setFileUrl($value) : '';
        }else{
            return "";
        }
        
    }
}