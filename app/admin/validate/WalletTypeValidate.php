<?php

namespace app\admin\validate;

use think\Validate;

class WalletTypeValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'                 => 'require',
    'wallet_cardholder'  => 'require',
    'wallet_name'        => 'require',
    'wallet_number'      => 'require',
    'wallet_image'       => 'require',
    'wallet_free'        => 'require',
    'wallet_max'         => 'require',
    'wallet_min'         => 'require',
    'wallet_type'        => 'require',
    'wallet_status'      => 'require',
  ];

  protected $message  =   [
    'id.require'                => '数据ID不能为空',
    'wallet_cardholder.require' => '请输入持卡人姓名',
    'wallet_name.require'       => '请输入银行名称/USDT网络',
    'wallet_number.require'     => '请输入银行卡号/USDT地址',
    'wallet_image.require'      => '请上传USDT二维码',
    'wallet_free.require'       => '请输入手续费(%)',
    'wallet_max.require'        => '请输入最大数量',
    'wallet_min.require'        => '请输入最小数量',
    'wallet_type.require'       => '请选择账号类型',
    'wallet_status.require'     => '请选择账号状态',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['wallet_cardholder', 'wallet_name', 'wallet_number', 'wallet_free', 'wallet_max', 'wallet_min', 'wallet_type', 'wallet_status'],
    // 修改
    'Upgrade' => ['wallet_cardholder', 'wallet_name', 'wallet_number', 'wallet_free', 'wallet_max', 'wallet_min', 'wallet_type', 'wallet_status', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}