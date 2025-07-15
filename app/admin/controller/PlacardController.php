<?php

namespace app\admin\controller;

use app\AdminController;
use app\model\Placard;
use hg\apidoc\annotation as Apidoc;
use think\exception\ValidateException;
use app\admin\validate\PlacardValidate;

/**
 * @Apidoc\Title("公告管理")
 * Author: JackMater
 */
class PlacardController extends AdminController {
  
  /**
   * @Apidoc\Title("公告列表")
   * @Apidoc\Desc("获取公告列表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getPlacardList")
   * @Apidoc\Query("pageIndex", type="number", desc="页码")
   * @Apidoc\Query("pageSize", type="number", desc="数据条数")
   * @Apidoc\Query("placard_name", type="string", desc="公告名称")
   * @Apidoc\Query("placard_type", type="string", desc="公告类型")
   * @Apidoc\Query("placard_status", type="number", desc="公告状态")
   * @Apidoc\Returned("placard_name", type="string", desc="公告名称")
   * @Apidoc\Returned("placard_content", type="string", desc="公告内容")
   * @Apidoc\Returned("placard_type", type="string", desc="公告类型")
   * @Apidoc\Returned("placard_status", type="number", desc="公告状态")
   * @Apidoc\Returned("placard_sort", type="number", desc="公告排序")
   * @Apidoc\Returned("create_time", type="string", desc="创建时间")
   */
  public function getPlacardList() {
    $pageIndex                =  input('pageIndex'); # 分页页码
    $pageSize                 =  input('pageSize'); # 每页数据条数
    $placard_name             =  input('placard_name'); # 公告名称
    $placard_type             =  input('placard_type'); # 公告类型
    $placard_status           =  input('placard_status'); # 公告状态

    # 验证分页参数是否为空
    if (empty($pageIndex) || empty($pageSize)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '分页参数不能为空!']]);
    }

    if (!empty($placard_name)) {
      $data['placard_name']     =  $placard_name; # 公告名称
    }

    if (isset($placard_type)) {
      $data['placard_type']     =  $placard_type; # 公告名称
    }

    if (isset($placard_status)) {
      $data['placard_status']   =  $placarplacard_statusd_name; # 公告名称
    }

    $Placard = Placard::order('create_time', 'desc');

    if (!empty($data)) {
      # 获取数据
      $PlacardList = $Placard -> where($data) -> page($pageIndex, $pageSize) -> select();
      # 获取总条数
      $total = $Placard -> where($data) -> count();
    } else {
      # 获取数据
      $PlacardList = $Placard -> page($pageIndex, $pageSize) -> select();
      # 获取总条数
      $total = $Placard -> count();
    }

    if ($PlacardList) {
      return json(['code' => 1,'data' => ['data' => $PlacardList, 'total' => $total]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '没有找到你要的数据!']]);
    }
  }

  /**
   * @Apidoc\Title("修改公告")
   * @Apidoc\Desc("修改公告")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/UpgradePlacard")
   * @Apidoc\Query("id", type="number", desc="数据ID")
   * @Apidoc\Query("placard_name", type="string", desc="公告名称")
   * @Apidoc\Query("placard_content", type="string", desc="公告内容")
   * @Apidoc\Query("placard_type", type="string", desc="公告类型")
   * @Apidoc\Query("placard_status", type="number", desc="公告状态")
   * @Apidoc\Query("placard_sort", type="number", desc="公告排序")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   */
  public function UpgradePlacard() {
    $id                       =  input('id'); # 数据ID
    $data['placard_name']     =  input('placard_name'); # 公告名称
    $data['placard_content']  =  input('placard_content'); # 公告内容
    $data['placard_type']     =  input('placard_type'); # 公告类型
    $data['placard_status']   =  input('placard_status'); # 公告状态
    $data['placard_sort']     =  input('placard_sort'); # 公告排序

    try {
      validate(PlacardValidate::class) -> scene('Upgrade') -> check(array_merge($data, ['id' => $id]));
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Upgrade = Placard::where(['id' => $id]) -> update($data);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '更新成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '更新失败!']]);
    }
  }

  /**
   * @Apidoc\Title("新增公告")
   * @Apidoc\Desc("新增公告")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/AddPlacard")
   * @Apidoc\Query("placard_name", type="string", desc="公告名称")
   * @Apidoc\Query("placard_content", type="string", desc="公告内容")
   * @Apidoc\Query("placard_type", type="string", desc="公告类型")
   * @Apidoc\Query("placard_status", type="number", desc="公告状态")
   * @Apidoc\Query("placard_sort", type="number", desc="公告排序")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   */
  public function AddPlacard() {
    $data['placard_name']     =  input('placard_name'); # 公告名称
    $data['placard_content']  =  input('placard_content'); # 公告内容
    $data['placard_type']     =  input('placard_type'); # 公告类型
    $data['placard_status']   =  input('placard_status'); # 公告状态
    $data['placard_sort']     =  input('placard_sort'); # 公告排序
    $data['create_time']      =  date('Y-m-d H:i:s', time()); # 创建时间

    try {
      validate(PlacardValidate::class) -> scene('Create') -> check($data);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Upgrade = Placard::create($data);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '新增成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '新增失败!']]);
    }
  }

  /**
   * @Apidoc\Title("删除公告")
   * @Apidoc\Desc("删除公告")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/DeletePlacard")
   * @Apidoc\Query("id", type="number", desc="数据ID")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   */
  public function DeletePlacard() {
    $id              =  input('id'); # 数据ID

    try {
      validate(PlacardValidate::class) -> scene('Delete') -> check(['id' => $id]);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Upgrade = Placard::where(['id' => $id]) -> delete();

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '删除成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '删除失败!']]);
    }
  }
}