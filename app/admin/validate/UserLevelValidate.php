<?php

namespace app\admin\validate;

use think\Validate;

class UserLevelValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'              => 'require',
    'name'            => 'require',
    'level'           => 'require',
    'level_icon'      => 'require',
    'order_count'     => 'require',
    'min_withdrawal'  => 'require',
    'max_withdrawal'  => 'require',
    'is_default'      => 'require',
    'status'          => 'require'
  ];

  protected $message  =   [
    'id.require'              => '数据ID不能为空',
    'name.require'            => '请输入等级名称',
    'level.require'           => '请输入等级级别',
    'level_icon.require'      => '请上传等级图标',
    'order_count.require'     => '请输入订单数量',
    'min_withdrawal.require'  => '请输入最低提现金额',
    'max_withdrawal.require'  => '请输入最高提现金额',
    'is_default.require'      => '请选择是否默认等级',
    'status.require'          => '请选择等级状态'
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['name', 'level', 'level_icon', 'order_count', 'min_withdrawal', 'max_withdrawal', 'is_default', 'status'],
    // 修改
    'Upgrade' => ['name', 'level', 'level_icon', 'order_count', 'min_withdrawal', 'max_withdrawal', 'is_default', 'status', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}