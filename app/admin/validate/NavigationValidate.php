<?php

namespace app\admin\validate;

use think\Validate;

class NavigationValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'                   => 'require',
    'navigation_domain'    => 'require',
    'navigation_path'      => 'require',
    'navigation_sort'      => 'require',
    'navigation_status'    => 'require',
  ];

  protected $message  =   [
    'id.require'                   => '数据ID不得为空',
    'navigation_domain.require'    => '请输入导航域名',
    'navigation_path.require'      => '请输入跳转域名',
    'navigation_sort.require'      => '请输入排序编号',
    'navigation_status.require'    => '请选择导航状态',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['navigation_domain', 'navigation_path', 'navigation_sort', 'navigation_status'],
    // 修改
    'Upgrade' => ['navigation_domain', 'navigation_path', 'navigation_sort', 'navigation_status', 'id'],
    // 删除
    'Delete'  => ['id'],
  ];

}