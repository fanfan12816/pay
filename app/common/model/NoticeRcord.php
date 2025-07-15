<?php


namespace app\common\model;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 通知记录表
 * Class NoticeRcord
 */
class NoticeRcord extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    // 设置json类型字段
	protected $json = ['extra'];
    
    // 设置JSON数据返回数组
    protected $jsonAssoc = true;
   
}