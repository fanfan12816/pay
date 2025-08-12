<?php

namespace app\admin\controller;

use think\facade\Cache;
use app\AdminController;
use app\model\{AdminGroup,AdminMember};
use hg\apidoc\annotation as Apidoc;
use app\admin\validate\GroupValidate;
use think\exception\ValidateException;

/**
 * @Apidoc\Title("用户组管理")
 * Author: JackMater
 */
class AdminGroupController extends AdminController {

  /**
   * @Apidoc\Title("用户组列表")
   * @Apidoc\Desc("获取用户组列表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getAdminGroupList")
   * @Apidoc\Query("pageIndex", type="number", desc="页码")
   * @Apidoc\Query("pageSize", type="number", desc="每页数据条数")
   * @Apidoc\Query("auth_title", type="string", desc="用户组名称")
   * @Apidoc\Query("auth_status", type="string", desc="用户组状态")
   * @Apidoc\Returned("auth_title", type="string", desc="用户组名称")
   * @Apidoc\Returned("auth_code", type="string", desc="角色标识")
   * @Apidoc\Returned("auth_status", type="number", desc="用户组状态")
   * @Apidoc\Returned("auth_system", type="number", desc="是否系统")
   * @Apidoc\Returned("auth_permission", type="number", desc="授权菜单")
   * @Apidoc\Returned("auth_sort", type="number", desc="排序")
   * @Apidoc\Returned("release_time", type="string", desc="修改时间")
   * @Apidoc\Returned("create_time", type="string", desc="创建时间")
   */
  public function getAdminGroupList() {
    $pageIndex         =  input('pageIndex'); # 分页页码
    $pageSize          =  input('pageSize'); # 每页数据条数
    $auth_title        =  input('auth_title'); # 用户组名称
    $auth_status       =  input('auth_status'); # 用户组状态

    # 验证分页参数是否为空
    if (empty($pageIndex) || empty($pageSize)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '分页参数不能为空!']]);
    }
    
    # 用户组名称
    if (!empty($auth_title)) {
      $data['auth_title']   =   $auth_title;
    }

    # 用户组状态
    if (!empty($auth_status)) {
      $data['auth_status']   =   $auth_status;
    }
    
    # 权限控制 c
    $code=AdminMember::alias('a') -> join('admin_auth b', 'a.member_group = b.id') -> where(['a.member_id' => $this -> member_id]) -> value('b.auth_code');
    $authWhere[]=["auth_code","<>","admin"];
    if($code=="admin"){
        $authWhere=[];
    }
    
    if (!empty($data)) {
      # 获取数据
      $result = AdminGroup::where($authWhere) ->where($data) -> page($pageIndex, $pageSize) -> order('auth_sort', 'desc') -> select();

      # 总页数
      $total = AdminGroup::where($authWhere) ->where($data) -> count();
    } else {
      # 获取数据
      $result = AdminGroup::where($authWhere) ->page($pageIndex, $pageSize) -> order('auth_sort', 'desc') -> select();

      # 总页数
      $total = AdminGroup::where($authWhere) ->count();
    }

    foreach ($result as $key => $value) {
      $permission = explode(',', $value['auth_permission']);
      foreach ($permission as $k => $v) {
        $permission[$k] = intval($v);
      }
      $value['auth_permission'] = $permission;
    }

    if ($result) {
      return json(['code' => 1,'data' => ['data' => $result, 'total' => $total]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '没有找到你要的数据!']]);
    }
  }

  /**
   * @Apidoc\Title("修改用户组")
   * @Apidoc\Desc("修改用户组")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/UpgradeAdminGroup")
   * @Apidoc\Query("id", type="number", desc="数据ID")
   * @Apidoc\Query("auth_title", type="string", desc="用户组名称")
   * @Apidoc\Query("auth_code", type="string", desc="角色标识")
   * @Apidoc\Query("auth_status", type="number", desc="用户组状态")
   * @Apidoc\Query("auth_system", type="number", desc="是否系统")
   * @Apidoc\Query("auth_permission", type="number", desc="授权菜单")
   * @Apidoc\Query("auth_sort", type="number", desc="排序")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function UpgradeAdminGroup() {
    $data['auth_title']       =  input('auth_title'); # 用户组名称
    // $data['auth_code']        =  input('auth_code'); # 角色标识
    $data['auth_status']      =  input('auth_status'); # 用户组状态
    $data['auth_system']      =  input('auth_system'); # 是否系统
    $data['auth_permission']  =  input('auth_permission'); # 授权菜单
    $data['auth_sort']        =  input('auth_sort'); # 排序
    $data['release_time']     =  date('Y-m-d H:i:s'); # 排序
    $id                       =  input('id'); # 数据ID 
    $verify_code              =  input('verify_code'); # 验证码

    try {
      validate(GroupValidate::class) -> scene('Upgrade') -> check(array_merge($data, ['id' => $id, 'verify_code' => $verify_code]));
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    // 获取缓存的谷歌秘钥
    $AuthKey = Cache::get($this -> member_id . '_member_authkey');

    // 校验谷歌验证码
    if (!CheckGoogleAuthCode($AuthKey, $verify_code)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '您输入的验证码不正确!']]);
    }

    // // 校验用户组标识
    // $CheckCode = AdminGroup::where(['auth_code' => $data['auth_code']]) -> find();

    // // 如果存在
    // if ($CheckCode) {
    //   return json(['code' => 0, 'data' => ['code' => 0, 'message' => '该角色已存在,不可重复修改!']]);
    // }

    $Upgrade = AdminGroup::where(['id' => $id]) -> update($data);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '更新成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '更新失败!']]);
    }
  }

  /**
   * @Apidoc\Title("新增用户组")
   * @Apidoc\Desc("新增用户组")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/AddAdminGroup")
   * @Apidoc\Query("auth_title", type="string", desc="用户组名称")
   * @Apidoc\Query("auth_code", type="string", desc="角色标识")
   * @Apidoc\Query("auth_status", type="number", desc="用户组状态")
   * @Apidoc\Query("auth_system", type="number", desc="是否系统")
   * @Apidoc\Query("auth_permission", type="number", desc="授权菜单")
   * @Apidoc\Query("auth_sort", type="number", desc="排序")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function AddAdminGroup() {
    $data['auth_title']       =  input('auth_title'); # 用户组名称
    $data['auth_code']        =  input('auth_code'); # 角色标识
    $data['auth_remark']      =  input('auth_remark'); # 备注
    $data['auth_status']      =  input('auth_status'); # 用户组状态
    $data['auth_system']      =  input('auth_system'); # 是否系统
    $data['auth_permission']  =  input('auth_permission'); # 授权菜单
    $data['auth_sort']        =  input('auth_sort'); # 排序
    $verify_code              =  input('verify_code'); # 验证码

    try {
      validate(GroupValidate::class) -> scene('Create') -> check(array_merge($data, ['verify_code' => $verify_code]));
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    // 获取缓存的谷歌秘钥
    $AuthKey = Cache::get($this -> member_id . '_member_authkey');

    // 校验谷歌验证码
    if (!CheckGoogleAuthCode($AuthKey, $verify_code)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '您输入的验证码不正确!']]);
    }

    // 校验用户组标识
    $CheckCode = AdminGroup::where(['auth_code' => $data['auth_code']]) -> find();

    // 如果存在
    if ($CheckCode) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '该角色已存在,不可重复添加!']]);
    }

    $Create = AdminGroup::create($data);

    if ($Create) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '新增成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '新增失败!']]);
    }
  }

  /**
   * @Apidoc\Title("删除用户组")
   * @Apidoc\Desc("删除用户组")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/DeleteAdminGroup")
   * @Apidoc\Query("id", type="string", desc="数据ID")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function DeleteAdminGroup() {
    $id          =  input('id'); # 数据ID

    try {
      validate(GroupValidate::class) -> scene('Delete') -> check(['id' => $id]);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    // 校验当前删除用户组是否为系统用户组
    $RoleGroup = AdminGroup::where(['id' => $id]) -> find();

    if (intval($RoleGroup['auth_system']) > 0) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '系统用户组禁止删除!']]);
    }
    if ($RoleGroup['auth_code']=='mch'||$RoleGroup['auth_code']=='admin') {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '系统用户组禁止删除!']]);
    }

    $Upgrade = $RoleGroup -> delete();

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '删除成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '删除失败!']]);
    }
  }
}