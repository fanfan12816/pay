<?php

namespace app\admin\validate;

use think\Validate;

class SystemConfigValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'             => 'require',
    'config_name'    => 'require',
    'config_key'     => 'require',
    'config_value'   => 'require',
    'config_status'  => 'require',
  ];

  protected $message  =   [
    'config_name.require'     => '请输入配置名称',
    'config_key.require'      => '请输入配置Key',
    'config_value.require'    => '请输入配置内容',
    'config_status.require'   => '请选择配置状态',
    'id.require'              => '数据ID不得为空',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['config_name', 'config_key', 'config_value', 'config_status'],
    // 修改
    'Upgrade' => ['config_name', 'config_key', 'config_value', 'config_status', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}