<?php

namespace app\admin\controller;

use app\AdminController;
use hg\apidoc\annotation as Apidoc;
use think\exception\ValidateException;
use app\model\LangType as LangTypeModel;
use app\admin\validate\LangTypeValidate;

/**
 * @Apidoc\Title("多语言管理")
 * Author: JackMater
 */
class LangTypeController extends AdminController {

  /**
   * @Apidoc\Title("语言类型列表")
   * @Apidoc\Desc("获取语言类型列表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getLangTypeList")
   * @Apidoc\Query("pageIndex", type="number", desc="页码")
   * @Apidoc\Query("pageSize", type="number", desc="每页数据条数")
   * @Apidoc\Returned("lang_name", type="string", desc="语言名称")
   * @Apidoc\Returned("lang_code", type="string", desc="语言标识")
   * @Apidoc\Returned("lang_icon", type="string", desc="语言图标")
   * @Apidoc\Returned("lang_default", type="string", desc="是否为默认显示语言 1是 0否")
   */
  public function getLangTypeList() {
    $pageIndex         =  input('pageIndex'); # 分页页码
    $pageSize          =  input('pageSize'); # 每页数据条数

    # 验证分页参数是否为空
    if (empty($pageIndex) || empty($pageSize)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '分页参数不能为空!']]);
    }

    if (!empty($data)) {
      # 获取数据
      $result = LangTypeModel::where($data) -> page($pageIndex, $pageSize) -> order('lang_sort', 'asc') -> select();

      # 总页数
      $total = LangTypeModel::where($data) -> count();
    } else {
      # 获取数据
      $result = LangTypeModel::page($pageIndex, $pageSize) -> order('lang_sort', 'asc') -> select();

      # 总页数
      $total = LangTypeModel::count();
    }

    if ($result) {
      return json(['code' => 1,'data' => ['data' => $result, 'total' => $total]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '没有找到你要的数据!']]);
    }
  }

  /**
   * @Apidoc\Title("修改语言类型")
   * @Apidoc\Desc("修改语言类型")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/UpgradeLangType")
   * @Apidoc\Query("id", type="number", desc="数据ID")
   * @Apidoc\Query("lang_name", type="string", desc="语言名称")
   * @Apidoc\Query("lang_code", type="string", desc="语言标识")
   * @Apidoc\Query("lang_icon", type="string", desc="语言图标")
   * @Apidoc\Query("lang_sort", type="string", desc="语言排序")
   * @Apidoc\Query("lang_status", type="string", desc="状态: 1启用, 0禁用")
   * @Apidoc\Query("lang_default", type="string", desc="是否为默认显示语言 1是 0否")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function UpgradeLangType() {
    $data['lang_name']    =  input('lang_name'); # 语言名称
    $data['lang_code']    =  input('lang_code'); # 语言标识
    $data['lang_icon']    =  input('lang_icon'); # 语言图标
    $data['lang_sort']    =  input('lang_sort'); # 语言类型状态
    $data['lang_status']  =  input('lang_status'); # 语言状态
    $data['lang_default'] =  input('lang_default'); # 默认语言
    $data['release_time'] =  date('Y-m-d H:i:s', time()); # 修改时间
    $id                   =  input('id'); # 数据ID

    try {
      validate(LangTypeValidate::class) -> scene('Upgrade') -> check(array_merge($data, ['id' => $id]));
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    // 如果为默认语言
    if (intval($data['lang_default']) > 0) {
      // 去除旧默认
      LangTypeModel::where(['lang_default' => 1]) -> update(['lang_default' => 0]);
    }

    $Upgrade = LangTypeModel::where(['id' => $id]) -> update($data);

    // 更新缓存
    CacheLangType(true);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '更新成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '更新失败!']]);
    }
  }

  /**
   * @Apidoc\Title("新增语言类型")
   * @Apidoc\Desc("新增语言类型")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/CreateLangType")
   * @Apidoc\Query("lang_name", type="string", desc="语言名称")
   * @Apidoc\Query("lang_code", type="string", desc="语言标识")
   * @Apidoc\Query("lang_icon", type="string", desc="语言图标")
   * @Apidoc\Query("lang_sort", type="string", desc="语言排序")
   * @Apidoc\Query("lang_status", type="string", desc="状态: 1启用, 0禁用")
   * @Apidoc\Query("lang_default", type="string", desc="是否为默认显示语言 1是 0否")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function CreateLangType() {
    $data['lang_name']    =  input('lang_name'); # 语言名称
    $data['lang_code']    =  input('lang_code'); # 语言标识
    $data['lang_icon']    =  input('lang_icon'); # 语言图标
    $data['lang_sort']    =  input('lang_sort'); # 语言类型状态
    $data['lang_status']  =  input('lang_status'); # 语言状态
    $data['lang_default'] =  input('lang_default'); # 默认语言
    $data['release_time'] =  date('Y-m-d H:i:s', time()); # 修改时间

    try {
      validate(LangTypeValidate::class) -> scene('Create') -> check($data);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    // 如果为默认语言
    if (intval($data['lang_default']) > 0) {
      // 去除旧默认
      LangTypeModel::where(['lang_default' => 1]) -> update(['lang_default' => 0]);
    }

    $Create = LangTypeModel::create($data);

    // 更新缓存
    CacheLangType(true);

    if ($Create) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '新增成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '新增失败!']]);
    }
  }

  /**
   * @Apidoc\Title("删除语言类型")
   * @Apidoc\Desc("删除语言类型")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/DeleteLangType")
   * @Apidoc\Query("id", type="string", desc="数据ID")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function DeleteLangType() {
    $id          =  input('id'); # 数据ID

    try {
      validate(LangTypeValidate::class) -> scene('Delete') -> check(['id' => $id]);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Upgrade = LangTypeModel::where(['id' => $id]) -> delete();

    // 更新缓存
    CacheLangType(true);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '删除成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '删除失败!']]);
    }
  }

}