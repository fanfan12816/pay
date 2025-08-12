<?php

namespace app\admin\controller;

use app\AdminController;
use app\model\AdminMember;
use hg\apidoc\annotation as Apidoc;
use app\admin\validate\AdminValidate;
use think\exception\ValidateException;

/**
 * @Apidoc\Title("后台账号")
 * Author: JackMater
 */

class AdminMemberController extends AdminController {
  
  /**
   * @Apidoc\Title("管理员账号列表")
   * @Apidoc\Desc("管理员账号列表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getAdminMemberList")
   * @Apidoc\Query("pageIndex", type="number", desc="页码")
   * @Apidoc\Query("pageSize", type="number", desc="数据条数")
   * @Apidoc\Query("member_nickname", type="string", desc="用户昵称")
   * @Apidoc\Query("member_username", type="string", desc="会员账号")
   * @Apidoc\Query("member_status", type="number", desc="账号状态 1启用 0禁用")
   * @Apidoc\Query("toTime", type="string", desc="开始时间")
   * @Apidoc\Query("formTime", type="string", desc="结束时间")
   * @Apidoc\Returned("member_id", type="number", desc="用户唯一ID")
   * @Apidoc\Returned("member_nickname", type="string", desc="用户昵称")
   * @Apidoc\Returned("member_username", type="string", desc="会员账号")
   * @Apidoc\Returned("member_portrait", type="string", desc="头像地址")
   * @Apidoc\Returned("member_group", type="string", desc="用户组ID")
   * @Apidoc\Returned("member_auth_ip", type="string", desc="IP白名单")
   * @Apidoc\Returned("member_authkey", type="string", desc="谷歌验证码秘钥")
   * @Apidoc\Returned("member_status", type="number", desc="账户状态")
   * @Apidoc\Returned("member_online", type="number", desc="在线状态")
   * @Apidoc\Returned("next_time", type="string", desc="上次登录时间")
   * @Apidoc\Returned("create_time", type="string", desc="注册时间")
   */
  public function getAdminMemberList() {
    $pageIndex               =  input('pageIndex'); # 分页页码
    $pageSize                =  input('pageSize'); # 每页数据条数
    $toTime                  =  input('toTime'); # 开始时间
    $formTime                =  input('formTime'); # 结束时间
    $data['account_type']    =  2; # 账号类型固定位后台账号

    # 验证分页参数是否为空
    if (empty($pageIndex) || empty($pageSize)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '分页参数不能为空!']]);
    }

    # 昵称
    if (!empty(input('member_nickname'))) {
      $data['a.member_nickname'] =  input('member_nickname');
    }

    # 账户
    if (!empty(input('member_username'))) {
      $data['a.member_username'] =  input('member_username');
    }

    # 账户状态
    if (!empty(input('member_status'))) {
      $data['a.member_status'] =  input('member_status');
    }
    
    # 权限控制 c
    $code=AdminMember::alias('a') -> join('admin_auth b', 'a.member_group = b.id') -> where(['a.member_id' => $this -> member_id]) -> value('b.auth_code');
    // $authWhere[]=["member_username","<>","admin"];
    if($code=="admin"){
        $authWhere=[];
    }elseif($code=="super"){
        $authWhere[]=["member_username","<>","admin"];
    }else{
        $authWhere[]=["member_id","=",$this -> member_id];
    }
    
    # 验证时间
    if ($toTime && $formTime) {
      $Member = AdminMember::alias('a') -> join('admin_auth b', 'a.member_group = b.id') ->where($authWhere) -> field('a.*,b.auth_title') -> whereTime('a.create_time', 'between', [$toTime, $formTime]) -> order(['a.create_time' => 'desc']);
    } else {
      $Member = AdminMember::alias('a') -> join('admin_auth b', 'a.member_group = b.id') ->where($authWhere) -> field('a.*,b.auth_title') -> order(['a.create_time' => 'desc']);
    }

    if (!empty($data)) {
      # 获取数据
      $MemberList = $Member -> where($data) -> page($pageIndex, $pageSize) -> select();
      # 获取总条数
      $total = $Member -> where($data) -> count();
    } else {
      # 获取数据
      $MemberList = $Member -> page($pageIndex, $pageSize) -> select();
      # 获取总条数
      $total = $Member -> count();
    }

    foreach ($MemberList as $key => $value) {
      $value['google_qrcode'] = CreateQrCodeImages($value['member_username'], $value['member_authkey']);
    }

    if ($MemberList) {
      return json(['code' => 1,'data' => ['data' => $MemberList, 'total' => $total]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '没有找到你要的数据!']]);
    }
  }

  /**
   * @Apidoc\Title("修改管理员信息")
   * @Apidoc\Desc("修改管理员信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/UpgradeAdminMember")
   * @Apidoc\Query("member_id", type="string", desc="数据ID")
   * @Apidoc\Query("member_nickname", type="string", desc="用户昵称")
   * @Apidoc\Query("member_username", type="string", desc="会员账号")
   * @Apidoc\Query("member_portrait", type="string", desc="头像地址")
   * @Apidoc\Query("member_password", type="string", desc="登录密码")
   * @Apidoc\Query("member_group", type="string", desc="用户组ID")
   * @Apidoc\Query("member_auth_ip", type="string", desc="IP白名单")
   * @Apidoc\Query("member_authkey", type="string", desc="谷歌验证码秘钥")
   * @Apidoc\Query("member_status", type="number", desc="账户状态")
   * @Apidoc\Query("google_status", type="number", desc="谷歌验证码状态")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   * @Apidoc\Returned("message", type="string", desc="提示内容")
   */
  public function UpgradeAdminMember() {
    $member_id                =  input('member_id'); # 数据ID
    $data['member_nickname']  =  input('member_nickname'); # 昵称
    $data['member_username']  =  input('member_username'); # 账户
    $data['member_portrait']  =  input('member_portrait'); # 头像地址
    $data['member_group']     =  input('member_group'); # 用户组ID
    $data['member_auth_ip']   =  input('member_auth_ip'); # IP白名单
    $data['member_authkey']   =  input('member_authkey'); # 谷歌验证码秘钥
    $data['member_status']    =  input('member_status'); # 账户状态
    $data['google_status']    =  input('google_status'); # 谷歌验证码状态
    $data['account_type']     =  2; # 账号类型
    $member_password          =  input('member_password'); # 登录密码

    # 头像
    if (empty($data['member_portrait'])) {
      $data['member_portrait']   =  '/UploadFile/Avater/default.gif';
    }

    try {
      validate(AdminValidate::class) -> scene('Upgrade') -> check(array_merge($data, ['member_id' => $member_id]));
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    # 如果需要修改密码
    if (!empty($member_password)) {
      # 加密密码
      $data['member_password']  =  encode($member_password);
    }

    $Upgrade = AdminMember::where(['member_id' => $member_id]) -> update($data);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '更新成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '更新失败!']]);
    }
  }

  /**
   * @Apidoc\Title("新增后台管理员")
   * @Apidoc\Desc("新增后台管理员")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/AddAdminMember")
   * @Apidoc\Query("member_nickname", type="string", desc="用户昵称")
   * @Apidoc\Query("member_username", type="string", desc="会员账号")
   * @Apidoc\Query("member_portrait", type="string", desc="头像地址")
   * @Apidoc\Query("member_password", type="string", desc="登录密码")
   * @Apidoc\Query("member_group", type="string", desc="用户组ID")
   * @Apidoc\Query("member_auth_ip", type="string", desc="IP白名单")
   * @Apidoc\Query("member_authkey", type="string", desc="谷歌验证码秘钥")
   * @Apidoc\Query("member_status", type="number", desc="账户状态")
   * @Apidoc\Query("google_status", type="number", desc="谷歌验证码状态")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   * @Apidoc\Returned("message", type="string", desc="提示内容")
   */
  public function AddAdminMember() {
    $data['member_nickname']  =  input('member_nickname'); # 昵称
    $data['member_username']  =  input('member_username'); # 账户
    $data['member_portrait']  =  input('member_portrait'); # 头像地址
    $data['member_group']     =  input('member_group'); # 用户组ID
    $data['member_auth_ip']   =  input('member_auth_ip'); # IP白名单
    $data['member_authkey']   =  CreateGoogleAuthKey(); # 谷歌验证码秘钥
    $data['member_status']    =  input('member_status'); # 账户状态
    $data['google_status']    =  input('google_status'); # 谷歌验证码状态
    $data['account_type']     =  2; # 账号类型
    $member_password          =  input('member_password'); # 登录密码
    $data['next_time']        =  date('Y-m-d H:i:s', time()); # 最后登录时间
    $data['create_time']      =  date('Y-m-d H:i:s', time()); # 注册时间

    # 头像
    if (empty($data['member_portrait'])) {
      $data['member_portrait']   =  '/UploadFile/Avater/default.gif';
    }

    # 密码
    if (!empty($member_password)) {
      # 加密密码
      $data['member_password']  =  encode($member_password);
    }

    try {
      validate(AdminValidate::class) -> scene('Create') -> check($data);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    # 验证用户名是否已存在
    $isCheck = AdminMember::where(['member_username' => $data['member_username']]) -> find();

    if ($isCheck) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '该账户已存在,换一个账户名再试试吧']]);
    }

    $Upgrade = AdminMember::create($data);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '新增成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '新增失败!']]);
    }
  }

  /**
   * @Apidoc\Title("删除管理员账户")
   * @Apidoc\Desc("删除管理员账户")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/DeleteAdminMember")
   * @Apidoc\Query("member_id", type="number", desc="用户唯一ID")
   * @Apidoc\Query("member_username", type="string", desc="会员账号")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   * @Apidoc\Returned("message", type="string", desc="提示内容")
   */
  public function DeleteAdminMember() {
    $data['member_id']         =   input('member_id'); # 数据ID

    try {
      validate(AdminValidate::class) -> scene('Delete') -> check($data);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    // 校验用户ID是否为系统超管
    if (intval($data['member_id']) === 1) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '系统管理员禁止删除!']]);
    }

    $Upgrade = AdminMember::where($data) -> delete();

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '删除成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '删除失败!']]);
    }
  }

  /**
   * @Apidoc\Title("创建谷歌验证码秘钥")
   * @Apidoc\Desc("创建谷歌验证码秘钥")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/CreateGoogleKey")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   * @Apidoc\Returned("message", type="string", desc="提示内容")
   */
  public function CreateGoogleKey() {

    $name   =   input('name'); # 昵称

    $AuthKey = CreateGoogleAuthKey();

    $QrCode = CreateQrCodeImages($name, $AuthKey);

    if ($AuthKey) {
      return json(['code' => 1, 'data' => ['code' => 1, 'key' => $AuthKey, 'qrcode' => $QrCode]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '创建失败!']]);
    }

  }
}