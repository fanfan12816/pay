<?php

namespace app\admin\validate;

use think\Validate;

class MusicListValidate extends Validate {

  // 验证规则
  protected $rule =   [
    'id'              => 'require',
    'class_id'        => 'require',
    'goods_name'      => 'require',
    'goods_info'      => 'require',
    'goods_singer'    => 'require',
    'goods_lyrics'    => 'require',
    'goods_compose'   => 'require',
    'goods_price'     => 'require',
    'goods_pic'       => 'require',
    'goods_url'       => 'require',
    'goods_min_time'  => 'require',
    'goods_status'    => 'require',
    'goods_sort'      => 'require',
  ];

  protected $message  =   [
    'id.require'               => '数据ID不能为空',
    'class_id.require'         => '请选择歌曲分类',
    'goods_name.require'       => '请输入歌曲名称',
    'goods_info.require'       => '请输入歌曲信息',
    'goods_singer.require'     => '请输入歌手名称',
    'goods_lyrics.require'     => '请输入作词人名称',
    'goods_compose.require'    => '请输入作曲人名称',
    'goods_price.require'      => '请输入打赏金额',
    'goods_pic.require'        => '请上传歌曲封面',
    'goods_url.require'        => '请输入歌曲链接',
    'goods_min_time.require'   => '请输入歌曲最少播放秒数',
    'goods_status.require'     => '请选择歌曲状态',
    'goods_sort.require'       => '请输入排序编号',
  ];


  // 验证类型
  protected $scene = [
    // 新增
    'Create'  => ['class_id', 'goods_name', 'goods_info', 'goods_singer', 'goods_lyrics', 'goods_compose', 'goods_price', 'goods_pic', 'goods_url', 'goods_min_time', 'goods_status', 'goods_sort'],
    // 修改
    'Upgrade' => ['class_id', 'goods_name', 'goods_info', 'goods_singer', 'goods_lyrics', 'goods_compose', 'goods_price', 'goods_pic', 'goods_url', 'goods_min_time', 'goods_status', 'goods_sort', 'id'],
    // 删除
    'Delete'  => ['id']
  ];

}