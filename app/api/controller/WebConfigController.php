<?php

namespace app\api\controller;

use app\BaseController;
use think\facade\Cache;
use hg\apidoc\annotation as Apidoc;

/**
 * @Apidoc\Title("站点配置")
 * Author: JackMater
 */
class WebConfigController extends BaseController {

  /**
   * @Apidoc\Title("站点信息")
   * @Apidoc\Desc("获取站点信息接口")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("api/v1/getSystemConfig")
   * @Apidoc\Returned("website_name", type="string", desc="站点名称")
   * @Apidoc\Returned("website_desc", type="string", desc="站点描述信息")
   * @Apidoc\Returned("website_keywords", type="string", desc="站点搜索关键词")
   * @Apidoc\Returned("website_favicon", type="string", desc="窗口角标")
   * @Apidoc\Returned("website_logo", type="string", desc="站点logo")
   * @Apidoc\Returned("website_copyright", type="string", desc="版权信息")
   * @Apidoc\Returned("website_beian", type="string", desc="网站备案信息")
   * @Apidoc\Returned("lang_type", type="string", desc="语言类型")
   * @Apidoc\Returned("country", type="string", desc="国家列表")
   */
  public function getSystemConfig() {
    # 数据库拿
    $Result = CacheWebSite();

    // 获取语言类型
    $Result['lang_type'] = CacheLangType();

    // 获取国家列表
    $Result['country'] = CacheCountry();

    // 获取客服列表
    $Result['coustem'] = CacheCoustemServer();

    // 维护内容
    $Result['maintain_content'] = CacheSystemConfig('maintain_content');

    // 站点状态
    $Result['website_status'] = CacheSystemConfig('website_status');
    
    // 是否允许注册
    $Result['register_status'] = CacheSystemConfig('register_status');

    if ($Result) {
      return json(['code' => 1, 'data' => $Result]);
    } else {
      return json(['code' => 0, 'message' => lang('not_data')]);
    }
  }

  /**
   * @Apidoc\Title("多语言列表")
   * @Apidoc\Desc("获取多语言列表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("api/v1/getLangLocaleList")
   * @Apidoc\Returned("lang_name", type="string", desc="语言名称")
   * @Apidoc\Returned("lang_code", type="string", desc="语言标识")
   * @Apidoc\Returned("lang_icon", type="string", desc="语言图标")
   * @Apidoc\Returned("lang_default", type="string", desc="是否为默认显示语言 1是 0否")
   */
  public function getLangLocaleList() {

    // 获取列表
    $LocaleList = CacheLangType();

    if ($LocaleList) {
      return json(['code' => 1, 'data' => $LocaleList]);
    } else {
      return json(['code' => 0, 'message' => lang('not_data')]);
    }

  }
}