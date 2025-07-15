<?php

namespace app\admin\validate;

use think\Validate;

class CountryValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'              => 'require',
    'country_name'    => 'require',
    'country_en'      => 'require',
    'country_id'      => 'require',
    'country_code'    => 'require',
    'country_status'  => 'require',
    'country_sort'    => 'require',
  ];

  protected $message  =   [
    'id.require'               => '数据ID不能为空',
    'country_name.require'     => '请输入中文名称',
    'country_en.require'       => '请输入英文名称',
    'country_id.require'       => '请输入国家代码',
    'country_code.require'     => '请输入国家区号代码',
    'coustem_status.require'   => '请选择国家状态',
    'country_sort.require'     => '请输入排序编号',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['country_name', 'country_en', 'country_id', 'country_code', 'country_status', 'country_sort'],
    // 修改
    'Upgrade' => ['country_name', 'country_en', 'country_id', 'country_code', 'country_status', 'country_sort', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}