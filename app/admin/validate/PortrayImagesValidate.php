<?php

namespace app\admin\validate;

use think\Validate;

class PortrayImagesValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'                => 'require',
    'list_id'           => 'require',
    'images_url'        => 'require',
    'images_status'     => 'require',
    'images_sort'       => 'require',
  ];

  protected $message  =   [
    'id.require'              => '数据ID不能为空',
    'list_id.require'         => '请选择所属分类',
    'images_url.require'      => '请上传写真照片',
    'images_status.require'   => '请选择照片状态',
    'images_sort.require'     => '请输入排序编号',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['list_id', 'images_url', 'images_status', 'images_sort'],
    // 修改
    'Upgrade' => ['list_id', 'images_url', 'images_status', 'images_sort', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}