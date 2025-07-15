<?php

namespace app\admin\validate;

use think\Validate;

class AuthRuleValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'             => 'require',
    'rule_pid'       => 'require',
    'rule_title'     => 'require',
    'rule_ismenu'    => 'require',
    'rule_keepalive' => 'require',
    'rule_show'      => 'require',
    'rule_sort'      => 'require',
    'rule_status'    => 'require'
  ];

  protected $message  =   [
    'rule_pid.require'       => '请选择上级菜单',
    'rule_title.require'     => '请输入菜单名称',
    'rule_ismenu.require'    => '请选择菜单类型',
    'rule_keepalive.require' => '请选择是否启用缓存',
    'rule_show.require'      => '请选择是否显示',
    'rule_sort.require'      => '请输入排序编号',
    'rule_status.require'    => '请选择菜单状态',
    'id.require'             => '数据ID不得为空',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['rule_pid', 'rule_title', 'rule_ismenu', 'rule_keepalive', 'rule_show', 'rule_sort', 'rule_status'],
    // 修改
    'Upgrade' => ['rule_pid', 'rule_title', 'rule_ismenu', 'rule_keepalive', 'rule_show', 'rule_sort', 'rule_status', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}