<?php

namespace app\admin\validate;

use think\Validate;

class GoodsValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'             => 'require',
    'goods_name'     => 'require',
    'goods_info'     => 'require',
    'goods_price'    => 'require',
    'goods_pic'      => 'require',
    'good_status'    => 'require',
  ];

  protected $message  =   [
    'id.require'             => '数据ID不得为空',
    'goods_name.require'     => '请输入商品名称',
    'goods_info.require'     => '请输入商品描述',
    'goods_price.require'    => '请输入商品价格',
    'goods_pic.require'      => '请输入商品图片链接',
    'good_status.require'    => '请选择商品状态',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['goods_name', 'goods_info', 'goods_price', 'goods_pic', 'good_status'],
    // 修改
    'Upgrade' => ['goods_name', 'goods_info', 'goods_price', 'goods_pic', 'good_status', 'id'],
    // 删除
    'Delete'  => ['id'],
  ];

}