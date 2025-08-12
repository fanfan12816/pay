<?php

namespace app\model;

use think\Model;

class AdminMember extends Model {

  protected $table = 'order_admin_user';

  protected $hidden = [
    'member_password'
  ];

  // 拼接图片域名和图片路径
  public function getMemberPortraitAttr($value) {

    // 如果$value是相对路径或者已经包含完整的URL，则直接返回
    if (strpos($value, 'http') === 0) {
      return $value;
    }

    // 拼接图片域名和图片路径
    return getImageDomain() . $value;

  }

}