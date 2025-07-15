<?php

namespace app\admin\validate;

use think\Validate;

class CoustemServerValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'              => 'require',
    'coustem_icon'    => 'require',
    'coustem_path'    => 'require',
    'coustem_status'  => 'require',
    'coustem_sort'    => 'require',
  ];

  protected $message  =   [
    'id.require'               => '数据ID不能为空',
    'coustem_icon.require'     => '请上传轮播图片',
    'coustem_path.require'     => '请输入客服链接',
    'coustem_status.require'   => '请选择客服状态',
    'coustem_sort.require'     => '请输入排序编号',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['coustem_icon', 'coustem_path', 'coustem_status', 'coustem_sort'],
    // 修改
    'Upgrade' => ['coustem_icon', 'coustem_path', 'coustem_status', 'coustem_sort', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}