<?php


namespace app\common\model;

use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

/**
 * 机器人群列表
 * Class BotGroup
 */
class BotGroup extends BaseModel
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    // 设置json类型字段
	protected $json = ['extra'];
    
    // 设置JSON数据返回数组
    protected $jsonAssoc = true;
    
    public function channel()
    {
        return $this->hasOne(Channel::class, 'id','channel_id');
    }
    public function bank()
    {
        return $this->hasOne(ChannelBank::class, 'id','bank_id');
    }
    public function merchant()
    {
        return $this->hasOne(Merchant::class, 'id','mch_id');
    }
   
}