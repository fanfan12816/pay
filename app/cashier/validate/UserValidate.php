<?php

namespace app\api\validate;

use think\Validate;

class UserValidate extends Validate {

  // 用户名正则
  protected $regex = ['phone' => '/^[1][3,4,5,6.7,8,9][0-9]{9}$/'];

  // 用户验证规则
  protected $rule =   [
    'member_username'  => 'require|regex:phone',
    'member_password'  => 'require|min:6',
    'member_password'  => 'require|max:13',
    'verify_code'      => 'require|min:6',
    'verify_code'      => 'require|max:6'
  ];

  protected $message  =   [
    'member_username.require'     => '请输入11位中国大陆手机号码',
    'member_password.min'         => '请输入6-13位密码',
    'member_password.max'         => '请输入6-13位密码',
    'verify_code.require'         => '请输入6位验证码',
    'verify_code.max'             => '请输入6位验证码',
    'verify_code.min'             => '请输入6位验证码'
  ];


  // 手机登录验证
  protected $scene = [
    'SmsLogin'  => ['member_username', 'verify_code'], // 短信登录
    'PwdLogin'  => ['member_username', 'member_password'], // 密码登录
    'register'  => ['member_username', 'member_password', 'verify_code'], // 账号注册
  ];

}