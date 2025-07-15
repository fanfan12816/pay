<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

class Commercial extends Model {
  // 数据表
  protected $table = 'order_advertise';

  // 拼接图片域名和图片路径
  public function getAdImageAttr($value) {

    // 如果$value是相对路径或者已经包含完整的URL，则直接返回
    if (strpos($value, 'http') === 0) {
      return $value;
    }

    // 拼接图片域名和图片路径
    return getImageDomain() . $value;

  }
}