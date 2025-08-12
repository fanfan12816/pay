<?php

namespace app\admin\validate;

use think\Validate;

class MemberValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'member_id'            => 'require',
    'member_username'      => 'require',
    'member_password'      => 'require',
    'member_nickname'      => 'require',
    'pay_password'         => 'require',
    'member_balance'       => 'require',
    'order_num'            => 'require',
    'completed_num'        => 'require',
    'vip_level'            => 'require',
    'balance_status'       => 'require',
    'member_status'        => 'require',
    'member_certification' => 'require',
    'member_online'        => 'require',
  ];

  protected $message  =   [
    'member_id.require'            => '会员ID不得为空',
    'member_username.require'      => '请输入会员账号',
    'member_password.require'      => '请输入登录密码',
    'member_nickname.require'      => '请输入会员昵称',
    'pay_password.require'         => '请输入交易密码',
    'member_balance.require'       => '请输入账户余额',
    'order_num.require'            => '请输入接单数量',
    'completed_num.require'        => '请输入剩余接单数量',
    'vip_level.require'            => '请选择会员VIP等级',
    'balance_status.require'       => '请选择账户余额状态',
    'member_status.require'        => '请选择账户状态',
    'member_certification.require' => '请选择实名状态',
    'member_online.require'        => '请选择在线状态',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['member_username', 'member_password', 'member_nickname', 'pay_password', 'member_balance', 'order_num', 'completed_num', 'vip_level', 'balance_status', 'member_status', 'member_certification', 'member_online'],
    // 修改
    'Upgrade' => ['member_username', 'member_nickname', 'member_balance', 'order_num', 'completed_num', 'vip_level', 'balance_status', 'member_status', 'member_certification', 'member_online', 'member_id'],
    // 删除
    'Delete'  => ['member_id']
  ];

}