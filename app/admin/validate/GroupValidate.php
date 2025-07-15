<?php

namespace app\admin\validate;

use think\Validate;

class GroupValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'             => 'require',
    'auth_title'     => 'require',
    'auth_code'      => 'require',
    'auth_status'    => 'require',
    'auth_system'    => 'require',
    'auth_permission'=> 'require',
    'auth_sort'      => 'require',
    'verify_code'    => 'require',
  ];

  protected $message  =   [
    'auth_title.require'      => '请输入角色名称',
    'auth_code'               => '请输入角色标识',
    'auth_status.require'     => '请选择角色状态',
    'auth_system.require'     => '请选择是否系统',
    'auth_permission.require' => '请选择授权菜单',
    'auth_sort.require'       => '请输入排序编号',
    'verify_code.require'     => '请输入验证码',
    'id.require'              => '数据ID不得为空',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['auth_title', 'auth_code', 'auth_status', 'auth_system', 'auth_permission', 'auth_sort', 'verify_code'],
    // 修改
    'Upgrade' => ['auth_title', 'auth_status', 'auth_system', 'auth_permission', 'auth_sort', 'verify_code', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}