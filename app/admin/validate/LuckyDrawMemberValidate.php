<?php

namespace app\admin\validate;

use think\Validate;

class LuckyDrawMemberValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'             => 'require',
    'lucky_id'       => 'require',
    'member_id'      => 'require',
    'member_username'=> 'require',
    'draw_count'     => 'require',
    'draw_status'    => 'require',
  ];

  protected $message  =   [
    'id.require'             => '数据ID不得为空',
    'lucky_id.require'       => '请选择所属活动',
    'member_id.require'      => '请输入会员ID',
    'member_username.require'=> '请输入会员账号',
    'draw_count.require'     => '请输入剩余抽奖次数',
    'draw_status.require'    => '请选择抽奖状态',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['lucky_id', 'member_id', 'member_username', 'draw_count', 'draw_status'],
    // 修改
    'Upgrade' => ['lucky_id', 'member_id', 'member_username', 'draw_count', 'draw_status', 'id'],
    // 删除
    'Delete'  => ['id'],
  ];

}