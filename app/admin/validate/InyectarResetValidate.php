<?php

namespace app\admin\validate;

use think\Validate;

class InyectarResetValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'               => 'require',
    'inyectar_num'     => 'require',
    'inyectar_regular' => 'require',
    'inyectar_rate'    => 'require',
    'inyectar_amount'  => 'require',
    'inyectar_status'  => 'require',
  ];

  protected $message  =   [
    'id.require'               => '数据ID不能为空',
    'inyectar_num.require'     => '请输入打针订单编号',
    'inyectar_regular.require' => '请输入固定佣金',
    'inyectar_rate.require'    => '请输入佣金比例',
    'inyectar_amount.require'  => '请输入打针金额',
    'inyectar_status.require'  => '请选择打针状态',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['inyectar_num', 'inyectar_status'],
    // 修改
    'Upgrade' => ['inyectar_num', 'inyectar_status', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}