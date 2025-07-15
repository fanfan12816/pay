<?php

namespace app\admin\validate;

use think\Validate;

class MusicClassValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'              => 'require',
    'class_name'      => 'require',
    'class_status'    => 'require',
    'class_sort'      => 'require',
  ];

  protected $message  =   [
    'id.require'               => '数据ID不能为空',
    'class_name.require'       => '请输入分类名称',
    'class_status.require'     => '请选择分类状态',
    'class_sort.require'       => '请输入排序编号',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['class_name', 'class_status', 'class_sort'],
    // 修改
    'Upgrade' => ['class_name', 'class_status', 'class_sort', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}