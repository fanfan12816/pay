<?php


namespace app\common\model;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 通道表
 * Class MerchaChannelnt
 */
class Channel extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

   
}