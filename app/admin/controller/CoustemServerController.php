<?php

namespace app\admin\controller;

use app\AdminController;
use hg\apidoc\annotation as Apidoc;
use think\exception\ValidateException;
use app\admin\validate\CoustemServerValidate;
use app\model\CoustemServer as CoustemServerModel;

/**
 * @Apidoc\Title("客服管理")
 * Author: JackMater
 */
class CoustemServerController extends AdminController {

  /**
   * @Apidoc\Title("客服列表")
   * @Apidoc\Desc("获取客服列表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getCoustemServerList")
   * @Apidoc\Query("pageIndex", type="number", desc="页码")
   * @Apidoc\Query("pageSize", type="number", desc="每页数据条数")
   * @Apidoc\Query("coustem_status", type="number", desc="1启用 0禁用")
   * @Apidoc\Query("coustem_name", type="number", desc="客服名称")
   * @Apidoc\Returned("coustem_name", type="string", desc="客服名称")
   * @Apidoc\Returned("coustem_path", type="string", desc="客服链接")
   * @Apidoc\Returned("coustem_icon", type="string", desc="客服图标")
   * @Apidoc\Returned("coustem_sort", type="number", desc="排序编号")
   * @Apidoc\Returned("coustem_status", type="number", desc="1启用 0禁用")
   * @Apidoc\Returned("release_time", type="number", desc="修改时间")
   * @Apidoc\Returned("create_time", type="string", desc="创建时间")
   */
  public function getCoustemServerList() {
    $pageIndex      =  input('pageIndex'); # 分页页码
    $pageSize       =  input('pageSize'); # 每页数据条数
    $coustem_name   =  input('coustem_name'); # 客服名称
    $coustem_status =  input('coustem_status'); # 客服状态

    # 验证分页参数是否为空
    if (empty($pageIndex) || empty($pageSize)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '分页参数不能为空!']]);
    }

    # 客服状态
    if (isset($coustem_status)) {
      $data['coustem_status']   =   $coustem_status;
    }

    # 客服名称
    if (isset($coustem_name)) {
      $data['coustem_name']   =   $coustem_name;
    }

    if (!empty($data)) {
      # 获取数据
      $result = CoustemServerModel::where($data) -> page($pageIndex, $pageSize) -> order('coustem_sort', 'desc') -> select();

      # 总页数
      $total = CoustemServerModel::where($data) -> count();
    } else {
      # 获取数据
      $result = CoustemServerModel::page($pageIndex, $pageSize) -> order('coustem_sort', 'desc') -> select();

      # 总页数
      $total = CoustemServerModel::count();
    }

    if ($result) {
      return json(['code' => 1,'data' => ['data' => $result, 'total' => $total]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '没有找到你要的数据!']]);
    }
  }

  /**
   * @Apidoc\Title("更新客服")
   * @Apidoc\Desc("更新客服信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/UpgradeCoustemServer")
   * @Apidoc\Query("id", type="number", desc="数据ID")
   * @Apidoc\Query("coustem_name", type="string", desc="客服名称")
   * @Apidoc\Query("coustem_path", type="string", desc="客服链接")
   * @Apidoc\Query("coustem_icon", type="string", desc="客服图标")
   * @Apidoc\Query("coustem_sort", type="number", desc="排序编号")
   * @Apidoc\Query("coustem_status", type="number", desc="1启用 0禁用")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function UpgradeCoustemServer() {
    $data['coustem_name']     =  input('coustem_name'); # 客服名称
    $data['coustem_path']     =  input('coustem_path'); # 客服链接
    $data['coustem_icon']     =  input('coustem_icon'); # 客服图标
    $data['coustem_sort']     =  input('coustem_sort'); # 排序编号
    $data['coustem_status']   =  input('coustem_status'); # 客服状态
    $data['release_time']     =  date('Y-m-d H:i:s', time());
    $id                       =  input('id'); # 数据ID

    try {
      validate(CoustemServerValidate::class) -> scene('Upgrade') -> check(array_merge($data, ['id' => $id]));
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Upgrade = CoustemServerModel::where(['id' => $id]) -> update($data);

    // 更新缓存
    CacheCoustemServer(true);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '更新成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '更新失败!']]);
    }
  }

  /**
   * @Apidoc\Title("新增客服")
   * @Apidoc\Desc("新增客服信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/AddCoustemServer")
   * @Apidoc\Query("coustem_name", type="string", desc="客服名称")
   * @Apidoc\Query("coustem_path", type="string", desc="客服链接")
   * @Apidoc\Query("coustem_icon", type="string", desc="客服图标")
   * @Apidoc\Query("coustem_sort", type="number", desc="排序编号")
   * @Apidoc\Query("coustem_status", type="number", desc="1启用 0禁用")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function AddCoustemServer() {
    $data['coustem_name']     =  input('coustem_name'); # 客服名称
    $data['coustem_path']     =  input('coustem_path'); # 客服链接
    $data['coustem_icon']     =  input('coustem_icon'); # 客服图标
    $data['coustem_sort']     =  input('coustem_sort'); # 排序编号
    $data['coustem_status']   =  input('coustem_status'); # 客服状态

    try {
      validate(CoustemServerValidate::class) -> scene('Create') -> check($data);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Create = CoustemServerModel::create($data);

    // 更新缓存
    CacheCoustemServer(true);

    if ($Create) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '新增成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '新增失败!']]);
    }
  }

  /**
   * @Apidoc\Title("删除客服")
   * @Apidoc\Desc("删除客服信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/DeleteCoustemServer")
   * @Apidoc\Query("id", type="string", desc="数据ID")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function DeleteCoustemServer() {
    $id          =  input('id'); # 数据ID

    try {
      validate(CoustemServerValidate::class) -> scene('Delete') -> check(['id' => $id]);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Upgrade = CoustemServerModel::where(['id' => $id]) -> delete();

    // 更新缓存
    CacheCoustemServer(true);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '删除成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '删除失败!']]);
    }
  }
}