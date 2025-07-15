<?php

namespace app\admin\controller;

use think\facade\Db;
use think\facade\App;
use app\AdminController;
use think\facade\Session;
use hg\apidoc\annotation as Apidoc;
use app\common\Services\SystemDataBaseBackupServices;

/**
 * @Apidoc\Title("数据库管理")
 * Author: JackMater
 */
class SystemDatabaseController extends AdminController {

  /**
   * @Apidoc\Title("数据库列表")
   * @Apidoc\Desc("获取数据库列表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getDataBaseList")
   * @Apidoc\Returned("name", type="string", desc="数据表名")
   * @Apidoc\Returned("comment", type="string", desc="数据表备注")
   * @Apidoc\Returned("engine", type="string", desc="数据引擎")
   * @Apidoc\Returned("data_length", type="string", desc="数据大小")
   * @Apidoc\Returned("collation", type="string", desc="排序规则")
   * @Apidoc\Returned("rows", type="string", desc="数据行数")
   * @Apidoc\Returned("auto_increment", type="string", desc="下一个自增值")
   * @Apidoc\Returned("update_time", type="string", desc="更新时间")
   * @Apidoc\Returned("create_time", type="string", desc="创建时间")
   */
  public function getDataBaseList() {

    # 获取数据
    $result = Db::query('SHOW TABLE STATUS');

    // $result = $this -> services -> getDataList();

    $list = array_map('array_change_key_case', $result);

    $data = array();

    // 处理数字大小
    foreach ($list as $key => $value) {
      if (($value['data_length'] / 1024) < 1024) {
        // KB
        $value['data_length'] = bcadd(($value['data_length'] / 1024), 0, 2) . ' KB';
      } else if (($value['data_length'] / 1024 / 1024) < 1024) {
        // MB
        $value['data_length'] = bcadd(($value['data_length'] / 1024 / 1024), 0, 2) . ' MB';
      } else {
        // GB
        $value['data_length'] = bcadd(($value['data_length'] / 1024 / 1024 / 1024), 0, 2) . ' GB';
      }

      array_push($data, $value);
    }

    return json(['code' => 1, 'data' => ['data' => $data, 'total' => count($data)]]) -> Code(200);
  }

  /**
   * @Apidoc\Title("数据表详情")
   * @Apidoc\Desc("获取数据表详情")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getDataBaseInfo")
   * @Apidoc\Query("name", type="string", desc="数据表名")
   * @Apidoc\Returned("name", type="string", desc="数据表名")
   * @Apidoc\Returned("comment", type="string", desc="数据表备注")
   * @Apidoc\Returned("engine", type="string", desc="数据引擎")
   * @Apidoc\Returned("data_length", type="string", desc="数据大小")
   * @Apidoc\Returned("collation", type="string", desc="排序规则")
   * @Apidoc\Returned("rows", type="string", desc="数据行数")
   * @Apidoc\Returned("auto_increment", type="string", desc="下一个自增值")
   * @Apidoc\Returned("update_time", type="string", desc="更新时间")
   * @Apidoc\Returned("create_time", type="string", desc="创建时间")
   */
  public function getDataBaseInfo() {

    $table_name    =   input('name'); # 数据表名

    // 验证参数
    if (empty($table_name)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '请选择您要查看的表']]);
    }

    $Services = app() -> make(SystemDataBaseBackupServices::class);

    $data = $Services -> getRead($table_name);

    return json(['code' => 1, 'data' => ['data' => $data]]) -> Code(200);

  }

  /**
   * @Apidoc\Title("优化数据库表")
   * @Apidoc\Desc("优化数据库表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/UpdateOptimize")
   * @Apidoc\Query("name", type="string", desc="数据表名")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   * @Apidoc\Returned("message", type="string", desc="提示内容")
   */
  public function UpdateOptimize() {

    $table_name    =   input('name'); # 数据表名

    // 验证参数
    if (empty($table_name)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '请选择您要优化的表']]);
    }

    $Services = app() -> make(SystemDataBaseBackupServices::class);

    $Optimize = $Services -> getDbBackup() -> optimize($table_name);

    if ($Optimize) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '优化成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '优化失败']]);
    }

  }

  /**
   * @Apidoc\Title("修复数据库表")
   * @Apidoc\Desc("修复数据库表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/RepairDataBase")
   * @Apidoc\Query("name", type="string", desc="数据表名")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   * @Apidoc\Returned("message", type="string", desc="提示内容")
   */
  public function RepairDataBase() {

    $table_name    =   input('name'); # 数据表名

    // 验证参数
    if (empty($table_name)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '请选择您要修复的表']]);
    }

    $Services = app() -> make(SystemDataBaseBackupServices::class);

    $Repair = $Services -> getDbBackup() -> repair($table_name);

    if ($Repair) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '修复成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '修复失败']]);
    }

  }

  /**
   * @Apidoc\Title("备份数据库")
   * @Apidoc\Desc("备份数据库")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/BackUpDataBase")
   * @Apidoc\Query("name", type="string", desc="数据表名")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   * @Apidoc\Returned("message", type="string", desc="提示内容")
   */
  public function BackUpDataBase() {

    $table_name    =   input('name'); # 数据表名

    // 验证参数
    if (empty($table_name)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '请选择您要备份的表']]);
    }

    $Services = app() -> make(SystemDataBaseBackupServices::class);

    $BackUp = $Services -> backup($table_name);

    if ($BackUp) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '备份成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '备份失败']]);
    }

  }

  /**
   * @Apidoc\Title("备份记录")
   * @Apidoc\Desc("获取备份记录")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getBackupFileList")
   */
  public function getBackupFileList() {

    $Services = app() -> make(SystemDataBaseBackupServices::class);

    $data = $Services -> getBackup();

    return json(['code' => 1, 'data' => ['data' => $data, 'total' => count($data)]]);
  }

  /**
   * @Apidoc\Title("删除备份")
   * @Apidoc\Desc("删除备份记录")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/DeleteBackupFileList")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   * @Apidoc\Returned("message", type="string", desc="提示内容")
   */
  public function DeleteBackupFileList() {

    $FileName      =    input('file_name'); # 备份文件名称

    // 验证参数
    if (empty($FileName)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '请选择要删除的备份文件']]);
    }

    $Services = app() -> make(SystemDataBaseBackupServices::class);

    $Delete = $Services -> getDbBackup() -> delFile($FileName);

    if ($Delete) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '删除成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '删除失败']]);
    }
  }

}