<?php


namespace app\common\model;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 机器人发送记录
 * Class BotRecord
 */
class BotRecord extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    // 设置json类型字段
	protected $json = ['request_data','back_data'];
    
    // 设置JSON数据返回数组
    protected $jsonAssoc = true;
   
}