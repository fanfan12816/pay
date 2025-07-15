<?php

namespace app\api\validate;

use think\Validate;

class AppStartingValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'app_type'       => 'require',
    'app_version'    => 'require',
    'wgt_version'    => 'require',
    'language'       => 'require',
    'device_type'    => 'require',
    'device_name'    => 'require',
    'system_verison' => 'require',
    'network_type'   => 'require',
    'app_ua'         => 'require',
  ];

  protected $message  =   [
    'app_type.require'       => '请上传客户端系统类型',
    'app_version.require'    => '请上传APP版本',
    'wgt_version.require'    => '请上传资源包版本',
    'language.require'       => '请上传客户端语言',
    'device_type.require'    => '请上传设备类型',
    'device_name.require'    => '请上传手机型号',
    'system_verison.require' => '请上传系统版本',
    'network_type.require'   => '请上传网络类型',
    'app_ua.app_ua'          => '请上传APP内核UA'
  ];


  // 验证类型
  protected $scene = [
    // 提交
    'Create' => ['app_type', 'app_version', 'wgt_version', 'language', 'device_name', 'device_type', 'system_verison', 'network_type', 'app_ua'],
  ];

}