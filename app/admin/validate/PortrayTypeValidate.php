<?php

namespace app\admin\validate;

use think\Validate;

class PortrayTypeValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'                => 'require',
    'portray_name'      => 'require',
    'portray_status'    => 'require',
    'portray_sort'      => 'require',
  ];

  protected $message  =   [
    'id.require'               => '数据ID不能为空',
    'portray_name.require'     => '请输入公告名称',
    'portray_status.require'   => '请选择公告状态',
    'portray_sort.require'     => '请输入排序编号',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['portray_name', 'portray_status', 'portray_sort'],
    // 修改
    'Upgrade' => ['portray_name', 'portray_status', 'portray_sort', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}