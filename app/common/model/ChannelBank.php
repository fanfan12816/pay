<?php


namespace app\common\model;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;
use app\common\service\FileService;

/**
 * 通道银行卡表
 * Class ChannelBank bank_image
 */
class ChannelBank extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

   
   /**
     * @notes 图片地址拼接域名
     * @param $value
     * @return string
     */
    public function getBankImageAttr($value)
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
    public function setBankImageAttr($value)
    {
        if($value){
            return trim($value) ? FileService::setFileUrl($value) : '';
        }else{
            return "";
        }
    }
}