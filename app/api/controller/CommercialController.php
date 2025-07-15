<?php

namespace app\api\controller;

use app\model\Swipe;
use app\model\Placard;
use app\BaseController;
use think\facade\Cache;
use app\model\Commercial;
use hg\apidoc\annotation as Apidoc;

/**
 * @Apidoc\Title("广告/公告")
 * Author: JackMater
 */
class CommercialController extends BaseController {

  /**
   * @Apidoc\Title("广告列表")
   * @Apidoc\Desc("获取广告列表接口")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("api/v1/getCommercialList")
   * @Apidoc\Returned("ad_image", type="string", desc="广告图片链接")
   * @Apidoc\Returned("ad_path", type="string", desc="点击跳转地址")
   * @Apidoc\Returned("ad_isNewOpen", type="string", desc="1新窗口 0本窗口")
   * @Apidoc\Returned("ad_type", type="string", desc="1移动端 0PC端")
   */
  public function getCommercialList() {

    # 获取广告数据
    $Result = CacheCommercial();

    if ($Result) {
      return json(['code' => 1, 'data' => $Result]);
    } else {
      return json(['code' => 0, 'message' => lang('not_data')]);
    }

  }

  /**
   * @Apidoc\Title("公告列表")
   * @Apidoc\Desc("获取公告列表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("api/v1/getPlacardList")
   * @Apidoc\Returned("placard_name", type="string", desc="公告名称")
   * @Apidoc\Returned("placard_content", type="string", desc="公告内容")
   * @Apidoc\Returned("placard_type", type="string", desc="1弹窗 0普通")
   */
  public function getPlacardList() {

    # 数据库获取
    $Result = CachePlacard();

    if ($Result) {
      return json(['code' => 1, 'data' => $Result]);
    } else {
      return json(['code' => 0, 'message' => lang('not_data')]);
    }

  }

  /**
   * @Apidoc\Title("轮播列表")
   * @Apidoc\Desc("获取轮播列表接口")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("api/v1/getSwipeList")
   * @Apidoc\Returned("swipe_image", type="string", desc="图片地址")
   * @Apidoc\Returned("swipe_path", type="string", desc="跳转地址")
   * @Apidoc\Returned("swipe_isNewOpen", type="string", desc="1新窗口打开 0本窗口打开")
   * @Apidoc\Returned("swipe_type", type="string", desc="1移动端 0PC端")
   */
  public function getSwipeList() {
    
    # 获取数据
    $Result = CacheSwipe();

    if ($Result) {
      return json(['code' => 1, 'data' => $Result]);
    } else {
      return json(['code' => 0, 'message' => lang('not_data')]);
    }
  }

}