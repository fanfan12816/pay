<?php

namespace app\admin\validate;

use think\Validate;

class UserOrderValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'               => 'require',
    'order_status'     => 'require',
  ];

  protected $message  =   [
    'id.require'              => '数据ID不能为空',
    'order_status.require'    => '请选择订单状态',
  ];


  // 验证类型
  protected $scene = [
    // 取消
    'Cannel' => ['order_status', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}