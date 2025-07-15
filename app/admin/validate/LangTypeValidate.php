<?php

namespace app\admin\validate;

use think\Validate;

class LangTypeValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'             => 'require',
    'lang_name'      => 'require',
    'lang_code'      => 'require',
    'lang_icon'      => 'require',
    'lang_sort'      => 'require',
    'lang_status'    => 'require',
    'lang_default'   => 'require',
  ];

  protected $message  =   [
    'id.require'             => '数据ID不得为空',
    'lang_name.require'      => '请输入语言名称',
    'lang_code.require'      => '请输入语言标识',
    'lang_icon.require'      => '请上传语言图标',
    'lang_sort.require'      => '请输入排序编号',
    'lang_status.require'    => '请选择语言状态',
    'lang_default.require'   => '请选择是否为默认语言',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['lang_name', 'lang_code', 'lang_icon', 'lang_sort', 'lang_status', 'lang_default'],
    // 修改
    'Upgrade' => ['lang_name', 'lang_code', 'lang_icon', 'lang_sort', 'lang_status', 'lang_default', 'id'],
    // 删除
    'Delete'  => ['id'],
  ];

}