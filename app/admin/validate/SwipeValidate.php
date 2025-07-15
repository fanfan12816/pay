<?php

namespace app\admin\validate;

use think\Validate;

class SwipeValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'              => 'require',
    'swipe_image'     => 'require',
    'swipe_path'      => 'require',
    'swipe_isNewOpen' => 'require',
    'swipe_type'      => 'require',
    'swipe_status'    => 'require',
    'swipe_sort'      => 'require',
  ];

  protected $message  =   [
    'id.require'               => '数据ID不能为空',
    'swipe_image.require'      => '请上传轮播图片',
    'swipe_path.require'       => '请输入跳转地址',
    'swipe_isNewOpen.require'  => '请选择打开方式',
    'swipe_type.require'       => '请选择终端类型',
    'swipe_status.require'     => '请选择公告状态',
    'swipe_sort.require'       => '请输入排序编号',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['swipe_image', 'swipe_path', 'swipe_isNewOpen', 'swipe_type', 'swipe_status', 'swipe_sort'],
    // 修改
    'Upgrade' => ['swipe_image', 'swipe_path', 'swipe_isNewOpen', 'swipe_type', 'swipe_status', 'swipe_sort', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}