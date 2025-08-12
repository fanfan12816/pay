<?php

declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Swipe extends Model {
  // 数据表
  protected $table = 'order_swipe';

  // 拼接图片域名和图片路径
  public function getSwipeImageAttr($value) {

    // 如果$value是相对路径或者已经包含完整的URL，则直接返回
    if (strpos($value, 'http') === 0) {
      return $value;
    }

    // 拼接图片域名和图片路径
    return getImageDomain() . $value;

  }
}