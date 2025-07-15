<?php

namespace app\admin\controller;

use think\facade\Cache;
use app\AdminController;
use app\model\AdminMember;
use app\model\SystemConfig;
use hg\apidoc\annotation as Apidoc;
use think\exception\ValidateException;
use app\admin\validate\SystemConfigValidate;
use app\common\service\ConfigService;
use app\common\service\FileService;

/**
 * @Apidoc\Title("系统配置")
 * Author: JackMater
 */

class SystemConfigController extends AdminController {

  /**
   * @Apidoc\Title("系统配置列表")
   * @Apidoc\Desc("获取系统配置列表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getSystemConfigList")
   * @Apidoc\Query("pageIndex", type="number", desc="页码")
   * @Apidoc\Query("pageSize", type="number", desc="数据条数")
   * @Apidoc\Query("config_name", type="string", desc="配置名称")
   * @Apidoc\Query("config_key", type="string", desc="配置Key")
   * @Apidoc\Query("config_status", type="string", desc="配置状态 1启用 0禁用")
   * @Apidoc\Returned("config_name", type="string", desc="配置名称")
   * @Apidoc\Returned("config_key", type="string", desc="配置Key")
   * @Apidoc\Returned("config_value", type="string", desc="配置内容")
   * @Apidoc\Returned("config_status", type="number", desc="配置状态 1启用 0禁用")
   * @Apidoc\Returned("admin_name", type="string", desc="修改管理员")
   * @Apidoc\Returned("release_time", type="string", desc="修改时间")
   * @Apidoc\Returned("create_time", type="string", desc="创建时间")
   */
  public function getSystemConfigList() {
    $pageIndex          =  input('pageIndex'); # 分页页码
    $pageSize           =  input('pageSize'); # 每页数据条数
    $config_name        =  input('config_name'); # 配置名称
    $config_key         =  input('config_key'); # 配置Key
    $config_status      =  input('config_status'); # 配置状态

    # 验证分页参数是否为空
    if (empty($pageIndex) || empty($pageSize)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '分页参数不能为空!']]);
    }

    // 配置名称
    if (!empty($config_name)) {
      $data['config_name']    =  $config_name; # 配置名称
    }

    // 配置状态
    if (isset($config_status)) {
      $data['config_status']  =  $config_status; # 配置状态
    }

    // 配置Key
    if (!empty($config_key)) {
      $data['config_key']     =  $config_key; # 配置Key
    }

    $SystemConfigData = SystemConfig::order(['create_time' => 'desc']);

    if (!empty($data)) {
      # 获取数据
      $SystemConfigList = $SystemConfigData -> where($data) -> page($pageIndex, $pageSize) -> select();
      # 获取总条数
      $total = SystemConfig::where($data) -> count();
    } else {
      # 获取数据
      $SystemConfigList = $SystemConfigData -> page($pageIndex, $pageSize) -> select();
      # 获取总条数
      $total = SystemConfig::count();
    }

    # 处理数据
    foreach ($SystemConfigList as $key => $value) {
      # 管理员名称
      $value['admin_name'] = '';

      # 如果管理员ID不为空
      if ($value['admin_id']) {
        $value['admin_name'] = AdminMember::where(['member_id' => $value['admin_id']]) -> value('member_username');
      }
    }

    if ($SystemConfigList) {
      return json(['code' => 1,'data' => ['data' => $SystemConfigList, 'total' => $total]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '没有找到你要的数据!']]);
    }
  }

  /**
   * @Apidoc\Title("修改系统配置")
   * @Apidoc\Desc("修改系统配置")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/UpgradeSystemConfigList")
   * @Apidoc\Query("id", type="number", desc="数据ID")
   * @Apidoc\Query("config_name", type="string", desc="配置名称")
   * @Apidoc\Query("config_key", type="string", desc="配置Key")
   * @Apidoc\Query("config_value", type="string", desc="配置内容")
   * @Apidoc\Query("config_status", type="number", desc="配置状态 1启用 0禁用")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   */
  public function UpgradeSystemConfigList() {
    $id                      =  input('id'); # 数据ID
    $data['config_name']     =  input('config_name'); # 配置名称
    $data['config_key']      =  input('config_key'); # 配置Key
    $data['config_value']    =  input('config_value'); # 配置内容
    $data['config_status']   =  input('config_status'); # 配置状态
    $data['admin_id']        =  $this -> member_id; # 管理员ID
    $data['release_time']    =  date('Y-m-d H:i:s', time()); # 修改时间

    try {
      validate(SystemConfigValidate::class) -> scene('Upgrade') -> check(array_merge($data, ['id' => $id]));
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Upgrade = SystemConfig::where(['id' => $id]) -> update($data);

    // 更新缓存
    Cache::set($data['config_key'], $data['config_value']);

    if ($Upgrade) {
      return json(['code' => $Upgrade, 'data' => ['code' => 1, 'message' => '更新成功!']]);
    } else {
      return json(['code' => $Upgrade, 'data' => ['code' => 0, 'message' => '更新失败!']]);
    }
  }

  /**
   * @Apidoc\Title("新增系统配置")
   * @Apidoc\Desc("新增系统配置")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/AddSystemConfigList")
   * @Apidoc\Query("config_name", type="string", desc="配置名称")
   * @Apidoc\Query("config_key", type="string", desc="配置Key")
   * @Apidoc\Query("config_value", type="string", desc="配置内容")
   * @Apidoc\Query("config_status", type="number", desc="配置状态 1启用 0禁用")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   */
  public function AddSystemConfigList() {
    $data['config_name']     =  input('config_name'); # 配置名称
    $data['config_key']      =  input('config_key'); # 配置Key
    $data['config_value']    =  input('config_value'); # 配置内容
    $data['config_status']   =  input('config_status'); # 配置状态
    $data['admin_id']        =  $this -> member_id; # 管理员ID
    $data['release_time']    =  date('Y-m-d H:i:s', time()); # 修改时间

    try {
      validate(SystemConfigValidate::class) -> scene('Create') -> check($data);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Upgrade = SystemConfig::create($data);

    // 更新缓存
    Cache::set($data['config_key'], $data['config_value']);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '新增成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '新增失败!']]);
    }
  }

  /**
   * @Apidoc\Title("删除系统配置")
   * @Apidoc\Desc("删除系统配置")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/DeleteSystemConfigList")
   * @Apidoc\Query("id", type="number", desc="数据ID")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   */
  public function DeleteSystemConfigList() {
    $id              =  input('id'); # 数据ID

    try {
      validate(SystemConfigValidate::class) -> scene('Delete') -> check(['id' => $id]);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Config = SystemConfig::where(['id' => $id]) -> find();

    // 更新缓存
    Cache::delete($Config['config_key']);

    // 删除
    $DeleteConfig = $Config -> delete();

    if ($DeleteConfig) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '删除成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '删除失败!']]);
    }
  }
      /**
   * @Apidoc\Title("a站点信息")
   * @Apidoc\Desc("站点信息")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/webSite")
   * @Apidoc\Returned("website_name", type="string", desc="站点名称")
   * @Apidoc\Returned("website_description", type="string", desc="站点描述")
   * @Apidoc\Returned("website_keywords", type="string", desc="站点关键词")
   * @Apidoc\Returned("website_favicon", type="string", desc="站点角标")
   * @Apidoc\Returned("website_logo", type="string", desc="站点logo")
   * @Apidoc\Returned("website_login_logo", type="string", desc="站点登录logo")
   * @Apidoc\Returned("website_home", type="string", desc="首页Logo")
   * @Apidoc\Returned("website_copyright", type="string", desc="版权信息")
   */
  public function webSite() {
    $rest=[
        "website_name"=>ConfigService::get("website_name","shifang"),
        "website_description"=>ConfigService::get("website_description","shifang"),
        "website_favicon"=>FileService::getFileUrl(ConfigService::get("website_favicon","/static/tsl.png")),
        "website_logo"=>FileService::getFileUrl(ConfigService::get("website_logo","/static/tsl.png")),
        "website_login_logo"=>FileService::getFileUrl(ConfigService::get("website_login_logo","/static/tsl.png")),
        "website_home"=>FileService::getFileUrl(ConfigService::get("website_home","/static/tsl.png")),
        "website_copyright"=>ConfigService::get("website_copyright","shifang"),
    ];
    
    return ajaxReturn(1,"操作成功",$rest);
  }

}