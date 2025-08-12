<?php

namespace app\admin\validate;

use think\Validate;

class LuckyProductionValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'             => 'require',
    'lucky_id'       => 'require',
    'award_name'     => 'require',
    'award_desc'     => 'require',
    'award_icon'     => 'require',
    'award_money'    => 'require',
    'award_rate'     => ['require', 'between' => '0.0001,100'],
    'award_entity'   => 'require',
    'award_status'   => 'require',
    'award_sort'     => 'require',
  ];

  protected $message  =   [
    'id.require'             => '数据ID不得为空',
    'lucky_id.require'       => '请选择所属活动',
    'award_name.require'     => '请输入奖品名称',
    'award_desc.require'     => '请输入奖品说明',
    'award_icon.require'     => '请上传奖品图片',
    'award_money.require'    => '请输入奖品金额',
    'award_rate.require'     => '请输入中奖概率(0.0001-100%)',
    'award_rate.between'     => '中奖概率必须在0.0001% - 100%之间',
    'award_entity.require'   => '请选择是否实物',
    'award_status.require'   => '请选择活动状态',
    'award_sort.require'     => '请输入排序编号',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['lucky_id', 'award_name', 'award_desc', 'award_icon', 'award_money', 'award_rate', 'award_entity', 'award_status', 'award_sort'],
    // 修改
    'Upgrade' => ['lucky_id', 'award_name', 'award_desc', 'award_icon', 'award_money', 'award_rate', 'award_entity', 'award_status', 'award_sort', 'id'],
    // 删除
    'Delete'  => ['id'],
  ];

}