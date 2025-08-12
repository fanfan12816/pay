<?php

namespace app\admin\controller;

use think\facade\Cache;
use app\AdminController;
use app\model\WebConfig;
use hg\apidoc\annotation as Apidoc;

/**
 * @Apidoc\Title("站点管理")
 * Author: JackMater
 */
class WebConfigController extends AdminController {
  
  /**
   * @Apidoc\Title("站点信息列表")
   * @Apidoc\Desc("获取站点信息列表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getWebSiteConfig")
   * @Apidoc\Query("pageIndex", type="number", desc="页码")
   * @Apidoc\Query("pageSize", type="number", desc="每页数据条数")
   * @Apidoc\Query("website_name", type="string", desc="站点名称")
   * @Apidoc\Returned("website_name", type="string", desc="站点名称")
   * @Apidoc\Returned("website_description", type="string", desc="站点描述")
   * @Apidoc\Returned("website_keywords", type="string", desc="站点关键词")
   * @Apidoc\Returned("website_favicon", type="string", desc="站点角标")
   * @Apidoc\Returned("website_logo", type="string", desc="站点logo")
   * @Apidoc\Returned("website_home", type="string", desc="首页Logo")
   * @Apidoc\Returned("website_copyright", type="string", desc="版权信息")
   * @Apidoc\Returned("website_beian", type="string", desc="备案信息")
   * @Apidoc\Returned("create_time", type="string", desc="创建时间")
   */
  public function getWebSiteConfig() {
    $pageIndex               =  input('pageIndex'); # 分页页码
    $pageSize                =  input('pageSize'); # 每页数据条数
    $toTime                  =  input('toTime'); # 检测开始时间
    $formTime                =  input('formTime'); # 检测结束时间

    # 验证分页参数是否为空
    if (empty($pageIndex) || empty($pageSize)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '分页参数不能为空!']]);
    }

    # 站点名称
    if (!empty(input('website_name'))) {
      $data['website_name']  =  input('website_name');
    }

    # 验证时间
    if ($toTime && $formTime) {
      $WebSite = WebConfig::whereTime('create_time', 'between', [$toTime, $formTime]) -> order(['create_time' => 'desc']);
    } else {
      $WebSite = WebConfig::order(['create_time' => 'desc']);
    }

    if (!empty($data)) {
      # 获取数据
      $WebData = $WebSite -> where($data) -> page($pageIndex, $pageSize) -> select();
      # 获取总条数
      $total = $WebSite -> where($data) -> count();
    } else {
      # 获取数据
      $WebData = $WebSite -> page($pageIndex, $pageSize) -> select();
      # 获取总条数
      $total = $WebSite -> count();
    }

    if ($WebData) {
      return json(['code' => 1,'data' => ['data' => $WebData, 'total' => $total]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '没有找到你要的数据!']]);
    }
  }

  /**
   * @Apidoc\Title("新增站点")
   * @Apidoc\Desc("新增站点")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/AddSiteConfig")
   * @Apidoc\Query("website_name", type="string", desc="站点名称")
   * @Apidoc\Query("website_description", type="string", desc="站点描述")
   * @Apidoc\Query("website_keywords", type="string", desc="站点关键词")
   * @Apidoc\Query("website_favicon", type="string", desc="站点角标")
   * @Apidoc\Query("website_logo", type="string", desc="站点logo")
   * @Apidoc\Query("website_home", type="string", desc="首页Logo")
   * @Apidoc\Query("website_copyright", type="string", desc="版权信息")
   * @Apidoc\Query("website_beian", type="string", desc="备案信息")
   */
  public function AddSiteConfig() {
    $data['website_name']         =  input('website_name'); # 站点名称
    $data['website_description']  =  input('website_description'); # 站点描述
    $data['website_keywords']     =  input('website_keywords'); # 站点关键词
    $data['website_favicon']      =  input('website_favicon'); # 站点角标
    $data['website_logo']         =  input('website_logo'); # 站点logo
    $data['website_login_logo']   =  input('website_login_logo'); # 首页Logo
    $data['website_copyright']    =  input('website_copyright'); # 版权信息
    $data['website_beian']        =  input('website_beian'); # 备案信息
    $data['website_status']       =  input('website_status'); # 站点状态

    # 验证站点名称
    if (empty($data['website_name'])) {
      return json(['code'  => 0, 'data' => ['code'  => 0, 'message' => '请输入站点名称!']]);
    }

    $Upgrade = WebConfig::create($data);

    // 更新缓存数据
    CacheWebSite(true);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '新增成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '新增失败!']]);
    }
  }

  /**
   * @Apidoc\Title("更新站点信息")
   * @Apidoc\Desc("更新站点信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/UpgradeSiteConfig")
   * @Apidoc\Query("website_name", type="string", desc="站点名称")
   * @Apidoc\Query("website_description", type="string", desc="站点描述")
   * @Apidoc\Query("website_keywords", type="string", desc="站点关键词")
   * @Apidoc\Query("website_favicon", type="string", desc="站点角标")
   * @Apidoc\Query("website_logo", type="string", desc="站点logo")
   * @Apidoc\Query("website_home", type="string", desc="首页Logo")
   * @Apidoc\Query("website_copyright", type="string", desc="版权信息")
   * @Apidoc\Query("website_beian", type="string", desc="备案信息")
   */
  public function UpgradeSiteConfig() {
    $data['website_name']         =  input('website_name'); # 站点名称
    $data['website_description']  =  input('website_description'); # 站点描述
    $data['website_keywords']     =  input('website_keywords'); # 站点关键词
    $data['website_favicon']      =  input('website_favicon'); # 站点角标
    $data['website_logo']         =  input('website_logo'); # 站点logo
    $data['website_login_logo']   =  input('website_login_logo'); # 首页Logo
    $data['website_copyright']    =  input('website_copyright'); # 版权信息
    $data['website_beian']        =  input('website_beian'); # 备案信息
    $data['website_status']       =  input('website_status'); # 站点状态
    $id                           =  input('id'); # 数据ID

    # 验证站点名称
    if (empty($data['website_name'])) {
      return json(['code'  => 0, 'data' => ['code'  => 0, 'message' => '请输入站点名称!']]);
    }

    $Upgrade = WebConfig::where(['id' => $id]) -> update($data);

    // 更新缓存数据
    CacheWebSite(true);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '更新成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '更新失败!']]);
    }
  }

  /**
   * @Apidoc\Title("删除站点")
   * @Apidoc\Desc("删除站点")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/DeleteSiteConfig")
   * @Apidoc\Query("id", type="string", desc="站点ID")
   * @Apidoc\Query("verify_code", type="number", desc="谷歌验证码")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function DeleteSiteConfig() {
    $id              =  input('id'); # 文章ID 
    // $verify_code     =  input('verify_code'); # 验证码

    // # 如果参数为空
    // if (empty($artcle_id)) {
    //   return json(['code' => 0, 'data' => ['code' => 0, 'message' => '文章ID不能为空!']]);
    // }

    // if (empty($verify_code)) {
    //   return json(['code' => 0, 'data' => ['code' => 0, 'message' => '请输入验证码!']]);
    // }

    // // 获取缓存的谷歌秘钥
    // $AuthKey = Cache::get($this -> member_id . '_member_authkey');

    // // 校验谷歌验证码
    // if (!CheckGoogleAuthCode($AuthKey, $verify_code)) {
    //   return json(['code' => 0, 'data' => ['code' => 0, 'message' => '您输入的验证码不正确!']]);
    // }

    $Upgrade = WebConfig::where(['id' => $id]) -> delete();

    // 更新缓存数据
    CacheWebSite(true);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '删除成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '删除失败!']]);
    }
  }
}