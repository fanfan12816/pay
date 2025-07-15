<?php

namespace app\mch\validate;

use app\common\validate\BaseValidate;


/**
 * 商户管理验证
 * Class MerchantValidate
 */
class MerchantValidate extends BaseValidate
{
    protected $rule = [
        'nick_name' => 'require|chsAlphaNum|length:4,12',
        'avatar' => 'require',
        'ip_white' => 'require',
        'timezone' => 'require',
        'password' => 'require',
        'password'         => 'require|length:6,32|edit',
        'password_new'      => 'require|length:6,32|edit',
        'password_confirm' => 'requireWith:password_new|confirm:password_new',
    ];

    protected $message = [
        'nick_name.require'=> '商户昵称不能为空',
        'nick_name.chsAlphaNum' => '商户昵称只能是汉字、字母和数字',
        'nick_name.length' => '商户昵称长度须在4-12位字符',
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
     * @notes 修改个人资料场景
     */
    public function sceneUpdate()
    {
        return $this->only(['nick_name','ip_white','timezone'])->remove('ip_white', 'require');
    }
    /**
     * @notes 修改密码场景
     */
    public function scenePassword()
    {
        return $this->only(['password','password_new','password_confirm']);
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