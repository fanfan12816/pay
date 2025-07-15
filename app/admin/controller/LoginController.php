<?php

namespace app\admin\controller;

use app\model\AuthRule;
use think\facade\Cache;
use app\AdminController;
use app\model\AdminMember;
use think\captcha\facade\Captcha;
use hg\apidoc\annotation as Apidoc;
use app\admin\validate\AdminValidate;
use think\exception\ValidateException;

/**
 * @Apidoc\Title("登录")
 * Author: JackMater
 */
class LoginController extends AdminController {
  
  /**
   * @Apidoc\Title("登录")
   * @Apidoc\Desc("登录")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/LoginUser")
   * @Apidoc\Query("member_username", type="string", desc="用户名")
   * @Apidoc\Query("member_password", type="number", desc="密码")
   * @Apidoc\Query("verify_code", type="string", desc="验证码")
   * @Apidoc\Returned("token", type="string", desc="登录令牌")
   */
  public function LoginUser() {
    # 获取参数
    $data['a.member_username']  =  input('member_username'); # 获取用户名
    $data['a.member_password']  =  input('member_password'); # 获取密码
    $verify_code                =  input('verify_code'); # 获取验证码

    # 返回数据集
    $response = [
      ['code' => 0,'message' => '请输入验证码!'],
      ['code' => 0,'message' => '登录失败,您输入的账号或密码不正确!'],
      ['code' => 0,'message' => '您的账号已被管理员禁用!'],
      ['code' => 0,'message' => '验证码不正确!'],
      ['code' => 0,'message' => '用户名和密码不得为空!'],
      ['code' => 0,'message' => '当前IP未授权,禁止登录!'],
    ];

    # 验证码为空
    // if (empty($verify_code)) {
    //   return json($response[0]);
    // }

    #如果用户名和密码为空
    if (empty($data['a.member_username']) || empty($data['a.member_password'])) {
      return json($response[4]);
    }

    # 加密密码
    $data['a.member_password'] = encode($data['a.member_password']);

    #验证用户名和密码是否正确 用户是否存在
    $User = AdminMember::alias('a') -> join('admin_auth b', 'a.member_group = b.id') -> field('a.*, b.auth_permission') -> where($data) -> find();

    if (!$User) {
      return json($response[1]);
    }
    

    // 校验验证码是否正确
    if($User['google_status']!=0){
        # 验证码为空
        if (empty($verify_code)) {
          return json($response[0]);
        }
        if (!CheckGoogleAuthCode($User['member_authkey'], $verify_code)) {
          return json($response[3]);
        }
    }

    // IP白名单
    if (!CheckIPConfig($User['member_id'], $User['member_auth_ip'])) {
      return json($response[5]);
    }

    # 账号被禁用
    if ($User['member_status'] < 1) {
      return json($response[2]);
    }

    // 生成Token
    $Token = CreateAuthToken($User, 'logout_time_admin');

    // 缓存Token
    $RedisTokenKey = $User['member_id'] . '_' . app('http') -> getName() . '_Token';

    Cache::set($RedisTokenKey, $Token);

    // 缓存登录时间
    $LoginKey = $User['member_id'] . '_' . app('http') -> getName() . '_LoginTime';

    Cache::set($LoginKey, time());

    // 缓存谷歌秘钥
    Cache::set($User['member_id'] . '_member_authkey', $User['member_authkey']);

    $Client = getIPContent();

    AdminMember::where(['member_id' => $User['member_id']]) -> update([
      'next_time'       => date('Y-m-d H:i:s', time()),
      'member_online'   => 1,
      'member_ip'       => $Client['query'],
      'member_location' => $Client['location']
    ]);

    # 返回参数
    $Result = [
      'code' => 1,
      'data' => [
        'token'     =>  'Bearer ' . $Token,
      ],
      'message' => '登录成功!',
    ];

    return json($Result) ->Code(200);
  }

  /**
   * @Apidoc\Title("验证码")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("/api/v1/getCaptcha")
   */
  public function getCaptcha() {
    return Captcha::create();
  }

  /**
   * @Apidoc\Title("获取用户信息")
   * @Apidoc\Desc("获取用户信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getUserInfo")
   * @Apidoc\Returned("userId", type="string", desc="用户ID")
   * @Apidoc\Returned("username", type="number", desc="用户账号")
   * @Apidoc\Returned("realName", type="number", desc="用户昵称")
   * @Apidoc\Returned("avatar", type="string", desc="头像地址")
   * @Apidoc\Returned("desc", type="string", desc="管理员权限")
   * @Apidoc\Returned("homePath", type="string", desc="登录成功后跳转地址")
   * @Apidoc\Returned("roleName", type="string", desc="权限名称")
   * @Apidoc\Returned("value", type="string", desc="权限值")
   */
  public function getUserInfo() {
    # 如果请求头中有携带token
    if ($this -> member_id) {

      $User = AdminMember::alias('a') -> join('admin_auth b', 'a.member_group = b.id') -> field('a.*, b.auth_permission, b.auth_code, b.auth_title, b.id AS auth_id') -> where(['a.member_id' => $this -> member_id]) -> find();

      // 获取授权菜单列表
      $AuthRoleList = AuthRule::where(['rule_status' => 1]) -> where('id', 'in', $User['auth_permission']) -> field('id,rule_title AS title, rule_permission AS code, rule_ismenu AS type, rule_path AS path, rule_component AS component') -> select();

      # 返回参数
      $Result = [
        'code' => 1,
        'data' => [
          'userId'         =>  $User['member_id'],
          'username'       =>  $User['member_username'],
          'realName'       =>  $User['member_nickname'],
          'avatar'         =>  $User['member_portrait'],
          'desc'           =>  $User['auth_title'],
          // 'homePath'       =>  '/analysis',
          'time'           =>  date('Y-m-d H:i:s', time()),
          'roles'          =>  [
            'name'         =>  $User['auth_title'],
            'code'         =>  $User['auth_code'],
            'id'           =>  $User['auth_id']
          ],
          'permissionList' =>  $AuthRoleList,
        ],
        'message' => '登录成功!',
      ];

      return json($Result) ->Code(200);
      
    } else {
      # 未登录
      return json(['code' => 400, 'data' => ['code' => 400, 'message' => '您当前未登录!']]) -> Code(400);
    }
  }

  /**
   * @Apidoc\Title("退出登录")
   * @Apidoc\Desc("退出登录接口")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/Logout")
   * @Apidoc\Returned("code", type="string", desc="状态码")
   */
  public function Logout() {
    return json(['code' => 1, 'data' => ['code' => 1, 'message' => '退出登录成功!']]);
  }

}