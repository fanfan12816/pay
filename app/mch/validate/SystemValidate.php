<?php

namespace app\mch\validate;

use app\common\validate\BaseValidate;


/**
 * 订单管理验证
 * Class MerchantValidate
 */
class SystemValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|number',
        'scene_id' => 'require|between:1,2',
        'chat_id' => 'require',
        'extra' => 'require',
        'key' => 'require',
        'channel_id' => 'require',
    ];

    protected $message = [
        'channel_id.require'=> '通道号ID不能为空',
        'id.require'=> '修改id不能为空',
        'id.number'=> 'ID是数字',
        'scene_id.require' => '支付类型不能为空',
        'scene_id.between' => '支付类型只能是1或2',
        'chat_id.require'=> '群id不能为空',
        'extra.require'=> '配置不能为空',
        'key.require'=> '配置字段不能为空',
    ];
    
    /**
     * @notes 修改配置
     */
    public function sceneAddBot()
    {
        return $this->only(['scene_id','chat_id','channel_id']);
    }
    /**
     * @notes 修改配置
     */
    public function sceneDelBot()
    {
        return $this->only(['id']);
    }
    
    /**
     * @notes 修改配置
     */
    public function sceneUpdateBotExtra()
    {
        return $this->only(['id','key']);
    }
    /**
     * @notes 修改信息
     */
    public function sceneUpdateBot()
    {
        return $this->only(['id','scene_id','chat_id','channel_id']);
    }
    /**
     * @notes 修改信息
     */
    public function sceneLanguage()
    {
        return $this->only(['channel_id']);
    }



}