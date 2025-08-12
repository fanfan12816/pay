<?php

namespace app\admin\controller;

use app\AdminController;
use hg\apidoc\annotation as Apidoc;
use think\exception\ValidateException;
use app\model\Country as CountryModel;
use app\admin\validate\CountryValidate;

/**
 * @Apidoc\Title("国家管理")
 * Author: JackMater
 */
class CountryController extends AdminController {

  /**
   * @Apidoc\Title("国家列表")
   * @Apidoc\Desc("获取国家列表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getCountryList")
   * @Apidoc\Query("pageIndex", type="number", desc="页码")
   * @Apidoc\Query("pageSize", type="number", desc="每页数据条数")
   * @Apidoc\Query("country_status", type="number", desc="1启用 0禁用")
   * @Apidoc\Query("country_name", type="number", desc="中文名称")
   * @Apidoc\Returned("country_name", type="string", desc="中文名称")
   * @Apidoc\Returned("country_en", type="string", desc="英文名称")
   * @Apidoc\Returned("country_id", type="string", desc="国家代码")
   * @Apidoc\Returned("country_code", type="string", desc="国家区号代码")
   * @Apidoc\Returned("country_sort", type="number", desc="排序编号")
   * @Apidoc\Returned("country_status", type="number", desc="1启用 0禁用")
   * @Apidoc\Returned("create_time", type="string", desc="创建时间")
   */
  public function getCountryList() {
    $pageIndex      =  input('pageIndex'); # 分页页码
    $pageSize       =  input('pageSize'); # 每页数据条数
    $country_name   =  input('country_name'); # 中文名称
    $country_en     =  input('country_en'); # 英文名称
    $country_status =  input('country_status'); # 国家状态

    # 验证分页参数是否为空
    if (empty($pageIndex) || empty($pageSize)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '分页参数不能为空!']]);
    }

    # 国家状态
    if (isset($country_status)) {
      $data['a.country_status']   =   $country_status;
    }

    # 国家名称
    if (!empty($country_name)) {
      $data['a.country_name']   =   $country_name;
    }

    # 英文名称
    if (!empty($country_en)) {
      $data['a.country_en']     =   $country_en;
    }

    $Country = CountryModel::alias('a') -> join('admin_user b', 'a.admin_id = b.member_id') -> field('a.*, b.member_username AS admin_name') -> order('a.country_sort', 'desc');

    if (!empty($data)) {
      # 获取数据
      $result = $Country -> where($data) -> page($pageIndex, $pageSize) -> select();

      # 总页数
      $total = $Country -> where($data) -> count();
    } else {
      # 获取数据
      $result = $Country -> page($pageIndex, $pageSize) -> select();

      # 总页数
      $total = CountryModel::count();
    }

    if ($result) {
      return json(['code' => 1,'data' => ['data' => $result, 'total' => $total]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '没有找到你要的数据!']]);
    }
  }

  /**
   * @Apidoc\Title("更新国家")
   * @Apidoc\Desc("更新国家信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/UpgradeCountry")
   * @Apidoc\Query("id", type="number", desc="数据ID")
   * @Apidoc\Query("country_name", type="string", desc="中文名称")
   * @Apidoc\Query("country_en", type="string", desc="英文名称")
   * @Apidoc\Query("country_id", type="string", desc="国家代码")
   * @Apidoc\Query("country_code", type="string", desc="国家区号代码")
   * @Apidoc\Query("country_sort", type="number", desc="排序编号")
   * @Apidoc\Query("country_status", type="number", desc="1启用 0禁用")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function UpgradeCountry() {
    $data['country_name']     =  input('country_name'); # 中文名称
    $data['country_en']       =  input('country_en'); # 英文名称
    $data['country_id']       =  input('country_id'); # 国家代码
    $data['country_code']     =  input('country_code'); # 国家区号代码
    $data['country_sort']     =  input('country_sort'); # 排序编号
    $data['country_status']   =  input('country_status'); # 国家状态
    $data['admin_id']         =  $this -> member_id; # 管理员ID
    $data['release_time']     =  date('Y-m-d H:i:s', time()); # 修改时间
    $id                       =  input('id'); # 数据ID

    try {
      validate(CountryValidate::class) -> scene('Upgrade') -> check(array_merge($data, ['id' => $id]));
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Upgrade = CountryModel::where(['id' => $id]) -> update($data);

    // 更新缓存
    CacheCountry(true);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '更新成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '更新失败!']]);
    }
  }

  /**
   * @Apidoc\Title("新增国家")
   * @Apidoc\Desc("新增国家信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/AddCountry")
   * @Apidoc\Query("country_name", type="string", desc="中文名称")
   * @Apidoc\Query("country_en", type="string", desc="英文名称")
   * @Apidoc\Query("country_id", type="string", desc="国家代码")
   * @Apidoc\Query("country_code", type="string", desc="国家区号代码")
   * @Apidoc\Query("country_sort", type="number", desc="排序编号")
   * @Apidoc\Query("country_status", type="number", desc="1启用 0禁用")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function AddCountry() {
    $data['country_name']     =  input('country_name'); # 中文名称
    $data['country_en']       =  input('country_en'); # 英文名称
    $data['country_id']       =  input('country_id'); # 国家代码
    $data['country_code']     =  input('country_code'); # 国家区号代码
    $data['country_sort']     =  input('country_sort'); # 排序编号
    $data['country_status']   =  input('country_status'); # 国家状态
    $data['admin_id']         =  $this -> member_id; # 管理员ID
    $data['release_time']     =  date('Y-m-d H:i:s', time()); # 修改时间

    try {
      validate(CountryValidate::class) -> scene('Create') -> check($data);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Create = CountryModel::create($data);

    // 更新缓存
    CacheCountry(true);

    if ($Create) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '新增成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '新增失败!']]);
    }
  }

  /**
   * @Apidoc\Title("删除国家")
   * @Apidoc\Desc("删除国家信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/DeleteCountry")
   * @Apidoc\Query("id", type="string", desc="数据ID")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function DeleteCountry() {
    $id          =  input('id'); # 数据ID

    try {
      validate(CountryValidate::class) -> scene('Delete') -> check(['id' => $id]);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Upgrade = CountryModel::where(['id' => $id]) -> delete();

    // 更新缓存
    CacheCountry(true);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '删除成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '删除失败!']]);
    }
  }
}