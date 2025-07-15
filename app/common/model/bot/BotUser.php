<?php

namespace app\common\model\bot;


use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 机器人用户模型
 * Class Bot
 * @package app\common\model\bot;
 */
class BotUser extends BaseModel
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';

}