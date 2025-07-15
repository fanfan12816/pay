<?php

namespace app\admin\validate;

use think\Validate;

class LuckyDrawValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'             => 'require',
    'lucky_name'     => 'require',
    'lucky_desc'     => 'require',
    'lucky_bgc'      => 'require',
    'start_time'     => 'require',
    'end_time'       => 'require',
    'lucky_status'   => 'require',
    'template_type'  => 'require',
  ];

  protected $message  =   [
    'id.require'             => '数据ID不得为空',
    'lucky_name.require'     => '请输入活动名称',
    'lucky_desc.require'     => '请输入活动描述',
    'lucky_bgc.require'      => '请上传活动背景图',
    'start_time.require'     => '请选择活动开始时间',
    'end_time.require'       => '请选择活动结束时间',
    'lucky_status.require'   => '请选择活动状态',
    'template_type.require'  => '请选择抽奖模版类型',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['lucky_name', 'lucky_desc', 'lucky_bgc', 'start_time', 'end_time', 'lucky_status', 'template_type'],
    // 修改
    'Upgrade' => ['lucky_name', 'lucky_desc', 'lucky_bgc', 'start_time', 'end_time', 'lucky_status', 'template_type', 'id'],
    // 删除
    'Delete'  => ['id'],
  ];

}