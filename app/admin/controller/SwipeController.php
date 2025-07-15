<?php

namespace app\admin\controller;

use app\model\Swipe;
use app\AdminController;
use hg\apidoc\annotation as Apidoc;
use app\admin\validate\SwipeValidate;
use think\exception\ValidateException;

/**
 * @Apidoc\Title("轮播管理")
 * Author: JackMater
 */
class SwipeController extends AdminController {

  /**
   * @Apidoc\Title("轮播列表")
   * @Apidoc\Desc("获取轮播列表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getSwipeList")
   * @Apidoc\Query("pageIndex", type="number", desc="页码")
   * @Apidoc\Query("pageSize", type="number", desc="每页数据条数")
   * @Apidoc\Query("status", type="number", desc="1启用 0禁用")
   * @Apidoc\Query("isNewOpen", type="number", desc="1新窗口 0本窗口")
   * @Apidoc\Query("swipe_type", type="number", desc="1移动端 0PC端")
   * @Apidoc\Returned("swipe_image", type="string", desc="轮播图片链接")
   * @Apidoc\Returned("swipe_path", type="string", desc="点击跳转地址")
   * @Apidoc\Returned("swipe_isNewOpen", type="number", desc="1新窗口 0本窗口")
   * @Apidoc\Returned("swipe_type", type="number", desc="1移动端 0PC端")
   * @Apidoc\Returned("swipe_status", type="number", desc="1启用 0禁用")
   * @Apidoc\Returned("swipe_sort", type="number", desc="排序")
   * @Apidoc\Returned("create_time", type="string", desc="创建时间")
   */
  public function getSwipeList() {
    $pageIndex      =  input('pageIndex'); # 分页页码
    $pageSize       =  input('pageSize'); # 每页数据条数
    $status         =  input('status'); # 轮播状态
    $swipe_type     =  input('swipe_type'); # 终端类型
    $isNewOpen      =  input('isNewOpen'); # 是否新窗口打开

    # 验证分页参数是否为空
    if (empty($pageIndex) || empty($pageSize)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '分页参数不能为空!']]);
    }

    # 轮播状态
    if (isset($status)) {
      $data['swipe_status']   =   $status;
    }

    # 是否新窗口打开
    if (isset($isNewOpen)) {
      $data['swipe_isNewOpen']   =   $isNewOpen;
    }

    # 终端类型
    if (isset($swipe_type)) {
      $data['swipe_type']     =     $swipe_type;
    }

    if (!empty($data)) {
      # 获取数据
      $result = Swipe::where($data) -> page($pageIndex, $pageSize) -> order('swipe_sort', 'desc') -> select();

      # 总页数
      $total = Swipe::where($data) -> count();
    } else {
      # 获取数据
      $result = Swipe::page($pageIndex, $pageSize) -> order('swipe_sort', 'desc') -> select();

      # 总页数
      $total = Swipe::count();
    }

    if ($result) {
      return json(['code' => 1,'data' => ['data' => $result, 'total' => $total]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '没有找到你要的数据!']]);
    }
  }

  /**
   * @Apidoc\Title("更新轮播")
   * @Apidoc\Desc("更新轮播信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/UpgradeSwipe")
   * @Apidoc\Query("id", type="number", desc="数据ID")
   * @Apidoc\Query("swipe_image", type="string", desc="轮播图片链接")
   * @Apidoc\Query("swipe_path", type="string", desc="点击跳转地址")
   * @Apidoc\Query("swipe_isNewOpen", type="number", desc="是否新窗口打开 1新窗口 0本窗口")
   * @Apidoc\Query("swipe_type", type="number", desc="终端类型 1移动端 0PC端")
   * @Apidoc\Query("swipe_status", type="number", desc="轮播状态 1启用 0禁用")
   * @Apidoc\Query("swipe_sort", type="number", desc="排序")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function UpgradeSwipe() {
    $data['swipe_image']    =  input('swipe_image'); # 轮播图片链接
    $data['swipe_path']     =  input('swipe_path'); # 跳转地址
    $data['swipe_type']     =  input('swipe_type'); # 终端类型
    $data['swipe_isNewOpen']=  input('swipe_isNewOpen'); # 打开方式
    $data['swipe_status']   =  input('swipe_status'); # 轮播状态
    $data['swipe_sort']     =  input('swipe_sort');
    $id                     =  input('id'); # 数据ID

    try {
      validate(SwipeValidate::class) -> scene('Upgrade') -> check(array_merge($data, ['id' => $id]));
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Upgrade = Swipe::where(['id' => $id]) -> update($data);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '更新成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '更新失败!']]);
    }
  }

  /**
   * @Apidoc\Title("新增轮播")
   * @Apidoc\Desc("新增轮播信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/AddSwipe")
   * @Apidoc\Query("swipe_image", type="string", desc="轮播图片链接")
   * @Apidoc\Query("swipe_path", type="string", desc="点击跳转地址")
   * @Apidoc\Query("swipe_isNewOpen", type="number", desc="是否新窗口打开 1新窗口 0本窗口")
   * @Apidoc\Query("swipe_type", type="number", desc="终端类型 1移动端 0PC端")
   * @Apidoc\Query("swipe_status", type="number", desc="轮播状态 1启用 0禁用")
   * @Apidoc\Query("swipe_sort", type="number", desc="排序")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function AddSwipe() {
    $data['swipe_image']       =  input('swipe_image'); # 轮播图片链接
    $data['swipe_path']        =  input('swipe_path'); # 跳转地址
    $data['create_time']       =  date('Y-m-d H:i:s', time()); # 创建时间
    $swipe_isNewOpen           =  input('swipe_isNewOpen'); # 是否新窗口打开
    $swipe_type                =  input('swipe_type'); # 终端类型
    $swipe_status              =  input('swipe_status'); # 轮播状态
    $swipe_sort                =  input('swipe_sort'); # 排序

    try {
      validate(SwipeValidate::class) -> scene('Create') -> check($data);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Create = Swipe::create($data);

    if ($Create) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '新增成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '新增失败!']]);
    }
  }

  /**
   * @Apidoc\Title("删除轮播")
   * @Apidoc\Desc("删除轮播信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/DeleteSwipe")
   * @Apidoc\Query("id", type="string", desc="数据ID")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function DeleteSwipe() {
    $id          =  input('id'); # 数据ID

    try {
      validate(SwipeValidate::class) -> scene('Delete') -> check(['id' => $id]);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Upgrade = Swipe::where(['id' => $id]) -> delete();

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '删除成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '删除失败!']]);
    }
  }
}