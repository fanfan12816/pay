<?php

namespace app\admin\validate\merchant;

use app\common\validate\BaseValidate;
use app\common\model\{Merchant};


/**
 * 商户管理验证
 * Class MerchantValidate
 */
class MerchantValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'nick_name' => 'require|chsAlphaNum|length:4,12',
        'account' => 'require|alphaNum|length:4,12|unique:'.Merchant::class,
        'avatar' => 'require',
        'ip_white' => 'require',
        'timezone' => 'require',
        'password'         => 'require|length:6,32',
        'password_new'      => 'require|length:6,32|edit',
        'password_confirm' => 'requireWith:password_new|confirm:password_new',
        'debug' => 'in:0,1',
        'disable' => 'in:0,1',
        'action' => 'require|in:1,2',
        'change_amount' => 'require',
    ];

    protected $message = [
        'debug.in'=> '开启测试只能是0或1',
        'action.require'=> '动作不能为空',
        'change_amount.require'=> '变得数量不能为空',
        'id.require'=> '商户id不能为空',
        'account.require'=> '商户登录账号不能为空',
        'account.unique'=> '商户账号已经存在',
        'nick_name.require'=> '商户昵称不能为空',
        'nick_name.chsAlphaNum' => '商户昵称只能是汉字、字母和数字',
        'nick_name.length' => '商户昵称长度须在4-12位字符',
        'account.alphaNum' => '商户账号只能是字母和数字',
        'account.length' => '商户账号长度须在4-12位字符',
        'avatar.require'   => '商户头像不能为空',
        'ip_white.require' => '商户ip白名单不能为空',
        'timezone.require' => '商户时区不能为空',
        'password.require' => '密码不能为空',
        'password.length' => '密码长度须在6-32位字符',
        'password_new.require' => '新密码不能为空',
        'password_new.length' => '新密码长度须在6-32位字符',
        'password_confirm.requireWith' => '确认密码不能为空',
        'password_confirm.confirm' => '两次输入的密码不一致',
    ];
    
    /**
     * @notes 新增
     */
    public function sceneAdd()
    {
        return $this->only(['nick_name','account','password','timezone','ip_white','debug']);
    }
    /**
     * @notes 修改商户场景
     */
    public function sceneEdit()
    {
        return $this->only(['id','debug','disable','nick_name','password','ip_white','timezone'])->remove('ip_white', 'require')->remove('debug', 'require')->remove('nick_name', 'require')->remove('disable', 'require')->remove('password', 'require');
    }
    /**
     * @notes 修改密码场景
     */
    public function sceneDel()
    {
        return $this->only(['id']);
    }
    /**
     * @notes 修改密码场景
     */
    public function sceneMoney()
    {
        return $this->only(['id','action','change_amount']);
    }

   /**
     * @notes 验证密码不能一样
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     */
    public function edit($value, $rule, $data)
    {
        $password=$data['password']??"";
        $password_new=$data['password_new']??"";
        if ($password === $password_new) {
            return '新密码不能和旧密码一样';
        }
        $len = strlen($value);
        if ($len < 6 || $len > 32) {
            return '密码长度须在6-32位字符';
        }
        return true;
    }


}