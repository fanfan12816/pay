<?php

namespace app\admin\validate;

use think\Validate;

class AdminValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'member_id'         => 'require',
    'member_nickname'   => 'require',
    'member_username'   => 'require',
    'member_password'   => 'require',
    'member_auth_ip'    => 'require',
    'member_authkey'    => 'require',
    'member_group'      => 'require',
    'member_status'     => 'require'
  ];

  protected $message  =   [
    'member_id.require'         => '管理员ID不得为空',
    'member_nickname.require'   => '请输入管理员昵称',
    'member_username.require'   => '请输入管理员账号',
    'member_password.require'   => '请输入登录密码',
    'member_auth_ip.require'    => '请输入授权登录IP',
    'member_authkey.require'    => '请生成谷歌验证码秘钥',
    'member_group.require'      => '请选择用户组',
    'member_status.require'     => '请选择管理员账号状态'
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['member_nickname', 'member_username', 'member_password', 'member_auth_ip', 'member_authkey', 'member_group', 'member_status'],
    // 修改
    'Upgrade' => ['member_nickname', 'member_username', 'member_auth_ip', 'member_authkey', 'member_group', 'member_status', 'member_id'],
    // 删除
    'Delete'  => ['member_id'],
    // 登录
    'Login'   => ['member_username', 'member_password', 'verify_code'],
  ];

}