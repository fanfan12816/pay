<?php

namespace app\admin\validate;

use think\Validate;

class UserCardValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'               => 'require',
    'member_username'  => 'require',
    'bank_cardholder'  => 'require',
    'bank_name'        => 'require',
    'bank_number'      => 'require',
    'bank_type'        => 'require',
    'bank_status'      => 'require',
  ];

  protected $message  =   [
    'id.require'              => '数据ID不能为空',
    'member_username.require' => '请输入会员账号',
    'bank_cardholder.require' => '请输入持卡人姓名',
    'bank_name.require'       => '请输入银行名称/USDT网络类型',
    'bank_number.require'     => '请输入银行卡号/USDT地址',
    'bank_type.require'       => '请选择账号类型',
    'bank_status.require'     => '请选择账号状态',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['name', 'member_username', 'bank_cardholder', 'bank_name', 'bank_number', 'bank_type', 'bank_status'],
    // 修改
    'Upgrade' => ['name', 'member_username', 'bank_cardholder', 'bank_name', 'bank_number', 'bank_type', 'bank_status', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}