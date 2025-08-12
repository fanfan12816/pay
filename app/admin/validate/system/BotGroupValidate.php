<?php

namespace app\admin\validate\system;

use app\common\validate\BaseValidate;


/**
 * 商户管理验证
 * Class MerchantValidate
 */
class BotGroupValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'mch_id' => 'require',
        'chat_id' => 'require',
        'channel_id' => 'require',
        'key' => 'require',
        'scene_id' => 'require|in:1,2,3,4',
    ];

    protected $message = [
        'id.require'=> '主键id不能为空',
        'channel_id.require'=> '通道id不能为空',
        'chat_id.require' => '飞机群id不能为空',
        'scene_id.in' => '状态只能是1-2-3-4',
        'key.require'=> '配置字段不能为空',
    ];
    
    /**
     * @notes 
     */
    public function sceneDel()
    {
        return $this->only(['id']);
    }
    /**
     * @notes 
     */
    public function sceneEdit()
    {
        return $this->only(['id','scene_id','chat_id','channel_id']);
    }
    public function sceneConfig()
    {
        return $this->only(['id','key']);
    }
    public function sceneAdd()
    {
        return $this->only(['scene_id','chat_id','channel_id']);
    }




}