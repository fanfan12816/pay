<?php


namespace app\common\model;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;
use app\common\service\FileService;

/**
 * 多语言
 * Class Language
 */
class Language extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    // 设置json类型字段
	protected $json = ['extra'];
    
    // 设置JSON数据返回数组
    protected $jsonAssoc = true;
   
   
   /**
     * @notes logo - 用于头像地址拼接域名
     * @param $value
     * @return string
     */
    public function getLogoAttr($value)
    {
        return trim($value) ? FileService::getFileUrl($value) : '';
    }
    
    /**
     * @notes 清除logo域名
     * @param $value
     * @param $data
     * @return array|string|string[]
     */
    public function setLogoAttr($value)
    {
        return trim($value) ? FileService::setFileUrl($value) : '';
    }
       /**
     * @notes logo - 用于头像地址拼接域名
     * @param $value
     * @return string
     */
    public function getBgImgAttr($value)
    {
        return trim($value) ? FileService::getFileUrl($value) : '';
    }
    
    /**
     * @notes 清除logo域名
     * @param $value
     * @param $data
     * @return array|string|string[]
     */
    public function setBgImgAttr($value)
    {
        return trim($value) ? FileService::setFileUrl($value) : '';
    }
}