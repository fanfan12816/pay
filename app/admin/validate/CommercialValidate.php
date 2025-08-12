<?php

namespace app\admin\validate;

use think\Validate;

class CommercialValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'           => 'require',
    'ad_image'     => 'require',
    'ad_path'      => 'require',
    'ad_isNewOpen' => 'require',
    'ad_type'      => 'require',
    'ad_status'    => 'require',
    'ad_sort'      => 'require',
  ];

  protected $message  =   [
    'id.require'            => '数据ID不能为空',
    'ad_image.require'      => '请上传广告图片',
    'ad_path.require'       => '请输入跳转地址',
    'ad_isNewOpen.require'  => '请选择打开方式',
    'ad_type.require'       => '请选择终端类型',
    'ad_status.require'     => '请选择广告状态',
    'ad_sort.require'       => '请输入排序编号',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['ad_image', 'ad_path', 'ad_isNewOpen', 'ad_type', 'ad_status', 'ad_sort'],
    // 修改
    'Upgrade' => ['ad_image', 'ad_path', 'ad_isNewOpen', 'ad_type', 'ad_status', 'ad_sort', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}