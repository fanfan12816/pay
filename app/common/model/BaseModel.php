<?php

namespace app\common\model;
use app\common\service\FileService;

use think\Model;

/**
 * 基础模型
 * Class BaseModel
 * @package app\common\model
 */
class BaseModel extends Model
{
    
    
   /**
     * @notes 图片地址拼接域名
     * @param $value
     * @return string
     */
    public function getImageAttr($value)
    {
            return trim($value) ? FileService::getFileUrl($value) : '';
        if($value){
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
            return trim($value) ? FileService::setFileUrl($value) : '';
        if($value){
        }else{
            return "";
        }
    }
}

?>