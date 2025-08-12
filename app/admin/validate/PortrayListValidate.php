<?php

namespace app\admin\validate;

use think\Validate;

class PortrayListValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'                => 'require',
    'type_id'           => 'require',
    'portray_title'     => 'require',
    'portray_flag'      => 'require',
    'portray_address'   => 'require',
    'portray_pic'       => 'require',
    'portray_status'    => 'require',
    'portray_sort'      => 'require',
  ];

  protected $message  =   [
    'id.require'               => '数据ID不能为空',
    'type_id.require'          => '请选择所属分类',
    'portray_title.require'    => '请输入写真标题',
    'portray_flag.require'     => '请输入写真标签',
    'portray_address.require'  => '请输入人物所在地址',
    'portray_pic.require'      => '请上传封面图片',
    'portray_status.require'   => '请选择写真状态',
    'portray_sort.require'     => '请输入排序编号',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['type_id', 'portray_title', 'portray_flag', 'portray_address', 'portray_pic', 'portray_status', 'portray_sort'],
    // 修改
    'Upgrade' => ['type_id', 'portray_title', 'portray_flag', 'portray_address', 'portray_pic', 'portray_status', 'portray_sort', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}