<?php

namespace app\admin\validate;

use think\Validate;

class PlacardValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'                => 'require',
    'placard_name'      => 'require',
    'placard_content'   => 'require',
    'placard_type'      => 'require',
    'placard_status'    => 'require',
    'placard_sort'      => 'require',
  ];

  protected $message  =   [
    'id.require'               => '数据ID不能为空',
    'placard_name.require'     => '请输入公告名称',
    'placard_content.require'  => '请输入公告内容',
    'placard_type.require'     => '请选择公告类型',
    'placard_status.require'   => '请选择公告状态',
    'placard_sort.require'     => '请输入排序编号',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['placard_name', 'placard_content', 'placard_type', 'placard_status', 'placard_sort'],
    // 修改
    'Upgrade' => ['placard_name', 'placard_content', 'placard_type', 'placard_status', 'placard_sort', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}