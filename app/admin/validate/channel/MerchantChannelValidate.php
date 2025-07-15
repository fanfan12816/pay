<?php

namespace app\admin\validate\channel;

use app\common\validate\BaseValidate;
use app\common\model\{Channel};


/**
 * 商户管理验证
 * Class MerchantValidate
 */
class MerchantChannelValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'mch_id' => 'require',
        'name' => 'require|unique:'.Channel::class,
        'status' => 'require|in:0,1',
        'source' => 'require|in:0,1',
        'in_status' => 'require|in:0,1',
        'out_status' => 'require|in:0,1',
    ];

    protected $message = [
        'id.require'=> 'ID不能为空',
        'mch_id.require'=> '商户ID不能为空',
        'name.require'=> '通道不能为空',
        'name.unique'=> '通道已经存在',
        'status.in'=> '状态只能是0或1',
        'source.in'=> '通道状态只能是0或1',
        'in_status.in'=> '代收状态只能是0或1',
        'out_status.in'=> '代付状态只能是0或1',
    ];
    
    /**
     * @notes 修改商户场景
     */
    public function sceneEdit()
    {
        return $this->only(['id','status','in_per','out_per','source','in_status']);
    }
    /**
     * @notes 修改密码场景
     */
    public function sceneDel()
    {
        return $this->only(['id']);
    }
   

}