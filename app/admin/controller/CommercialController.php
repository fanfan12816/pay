<?php

namespace app\admin\controller;

use app\AdminController;
use app\model\Commercial;
use hg\apidoc\annotation as Apidoc;
use think\exception\ValidateException;
use app\admin\validate\CommercialValidate;

/**
 * @Apidoc\Title("广告管理")
 * Author: JackMater
 */
class CommercialController extends AdminController {

  /**
   * @Apidoc\Title("广告列表")
   * @Apidoc\Desc("获取广告列表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getCommercial")
   * @Apidoc\Query("pageIndex", type="number", desc="页码")
   * @Apidoc\Query("pageSize", type="number", desc="每页数据条数")
   * @Apidoc\Query("status", type="number", desc="1启用 0禁用")
   * @Apidoc\Query("isNewOpen", type="number", desc="1新窗口 0本窗口")
   * @Apidoc\Query("ad_type", type="number", desc="1移动端 0PC端")
   * @Apidoc\Returned("ad_image", type="string", desc="广告图片链接")
   * @Apidoc\Returned("ad_path", type="string", desc="点击跳转地址")
   * @Apidoc\Returned("ad_isNewOpen", type="number", desc="1新窗口 0本窗口")
   * @Apidoc\Returned("ad_type", type="number", desc="1移动端 0PC端")
   * @Apidoc\Returned("ad_status", type="number", desc="1启用 0禁用")
   * @Apidoc\Returned("ad_sort", type="number", desc="排序")
   * @Apidoc\Returned("create_time", type="string", desc="创建时间")
   */
  public function getCommercial() {
    $pageIndex      =  input('pageIndex'); # 分页页码
    $pageSize       =  input('pageSize'); # 每页数据条数
    $status         =  input('status'); # 广告状态
    $ad_type        =  input('ad_type'); # 终端类型
    $isNewOpen      =  input('isNewOpen'); # 是否新窗口打开

    # 验证分页参数是否为空
    if (empty($pageIndex) || empty($pageSize)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '分页参数不能为空!']]);
    }

    # 广告状态
    if (isset($status)) {
      $data['ad_status']   =   $status;
    }

    # 是否新窗口打开
    if (isset($isNewOpen)) {
      $data['ad_isNewOpen']   =   $isNewOpen;
    }

    # 终端类型
    if (isset($ad_type)) {
      $data['ad_type']     =     $ad_type;
    }

    if (!empty($data)) {
      # 获取数据
      $result = Commercial::where($data) -> page($pageIndex, $pageSize) -> order('ad_sort', 'desc') -> select();

      # 总页数
      $total = Commercial::where($data) -> count();
    } else {
      # 获取数据
      $result = Commercial::page($pageIndex, $pageSize) -> order('ad_sort', 'desc') -> select();

      # 总页数
      $total = Commercial::count();
    }

    if ($result) {
      return json(['code' => 1,'data' => ['data' => $result, 'total' => $total]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '没有找到你要的数据!']]);
    }
  }

  /**
   * @Apidoc\Title("更新广告")
   * @Apidoc\Desc("更新广告信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/UpgradeCommercial")
   * @Apidoc\Query("id", type="number", desc="数据ID")
   * @Apidoc\Query("ad_image", type="string", desc="广告图片链接")
   * @Apidoc\Query("ad_path", type="string", desc="点击跳转地址")
   * @Apidoc\Query("ad_isNewOpen", type="number", desc="是否新窗口打开 1新窗口 0本窗口")
   * @Apidoc\Query("ad_type", type="number", desc="终端类型 1移动端 0PC端")
   * @Apidoc\Query("ad_status", type="number", desc="广告状态 1启用 0禁用")
   * @Apidoc\Query("ad_sort", type="number", desc="排序")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function UpgradeCommercial() {
    $data['ad_image']       =  input('ad_image'); # 广告图片链接
    $data['ad_path']        =  input('ad_path'); # 跳转地址
    $data['ad_type']        =  input('ad_type'); # 终端类型
    $data['ad_isNewOpen']   =  input('ad_isNewOpen');
    $data['ad_status']      =  input('ad_status');
    $data['ad_sort']        =  input('ad_sort');
    $id                     =  input('id'); # 数据ID

    try {
      validate(CommercialValidate::class) -> scene('Upgrade') -> check(array_merge($data, ['id' => $id]));
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Upgrade = Commercial::where(['id' => $id]) -> update($data);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '更新成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '更新失败!']]);
    }
  }

  /**
   * @Apidoc\Title("新增广告")
   * @Apidoc\Desc("新增广告信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/AddCommercial")
   * @Apidoc\Query("ad_image", type="string", desc="广告图片链接")
   * @Apidoc\Query("ad_path", type="string", desc="点击跳转地址")
   * @Apidoc\Query("ad_isNewOpen", type="number", desc="是否新窗口打开 1新窗口 0本窗口")
   * @Apidoc\Query("ad_type", type="number", desc="终端类型 1移动端 0PC端")
   * @Apidoc\Query("ad_status", type="number", desc="广告状态 1启用 0禁用")
   * @Apidoc\Query("ad_sort", type="number", desc="排序")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function AddCommercial() {
    $data['ad_image']       =  input('ad_image'); # 广告图片链接
    $data['ad_path']        =  input('ad_path'); # 跳转地址
    $data['create_time']    =  date('Y-m-d H:i:s', time()); # 创建时间
    $ad_isNewOpen           =  input('ad_isNewOpen'); # 是否新窗口打开
    $ad_type                =  input('ad_type'); # 终端类型
    $ad_status              =  input('ad_status'); # 广告状态
    $ad_sort                =  input('ad_sort'); # 排序

    try {
      validate(CommercialValidate::class) -> scene('Create') -> check($data);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Create = Commercial::create($data);

    if ($Create) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '新增成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '新增失败!']]);
    }
  }

  /**
   * @Apidoc\Title("删除广告")
   * @Apidoc\Desc("删除广告信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/DeleteCommercial")
   * @Apidoc\Query("id", type="string", desc="数据ID")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function DeleteCommercial() {
    $id                     =  input('id'); # 数据ID

    try {
      validate(CommercialValidate::class) -> scene('Delete') -> check(['id' => $id]);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Upgrade = Commercial::where(['id' => $id]) -> delete();

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '删除成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '删除失败!']]);
    }
  }
}