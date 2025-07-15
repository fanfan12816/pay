<?php


namespace app\common\model\bot;


use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 机器人聊天模型
 * Class Bot
 * @package app\common\model\bot;
 */
class BotMessage extends BaseModel
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';

    // 设置json类型字段
	protected $json = ['message'];
    
    // 设置JSON数据返回数组
    protected $jsonAssoc = true;
}