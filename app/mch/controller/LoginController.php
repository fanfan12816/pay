<?php

namespace app\mch\controller;

use app\model\{AuthRule,AdminGroup,AdminMember};
use think\facade\Cache;
use app\MchController;
use app\common\model\Merchant;
use think\captcha\facade\Captcha;
use hg\apidoc\annotation as Apidoc;
use app\mch\validate\AdminValidate;
use think\exception\ValidateException;
use app\common\cache\MchAccountSafeCache;
use app\common\service\ConfigService;

/**
 * @Apidoc\Title("登录")
 * Author: JackMater
 */
class LoginController extends MchController {
  
  /**
   * @Apidoc\Title("登录")
   * @Apidoc\Desc("登录")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/Login")
   * @Apidoc\Param("account", type="string", require=true, desc="用户名")
   * @Apidoc\Param("password", type="string",require=true, desc="密码")
   * @Apidoc\Param("verify_code", type="string",require=true, desc="谷歌验证码")
   * @Apidoc\Returned("token", type="string", desc="登录令牌")
   */
  public function Login() {
    # 获取参数
    $account   =  input('account'); # 获取用户名
    $password  =  input('password'); # 获取密码
    $google                =  input('verify_code'); # 获取验证码
    // $google    =  input('google'); # 获取谷歌验证码
    
    #如果用户名和密码为空
    if (empty($account) || empty($password)) {
        return ajaxReturn(400,'用户名和密码不得为空!');
    }
    
    // # 验证码为空
    // if (empty($verify_code)) {
    //     return ajaxReturn(401,'请输入验证码!');
    // }
    
    // // 校验验证码是否正确
    // if (!CheckVerifyCode( $verify_code)) {
    //     if($verify_code!=666666){
    //         return ajaxReturn(402,'验证码不正确!');
    //     }
    // }
    
    // 登录限制
    $config = [
        'login_restrictions' =>ConfigService::get('login_restrictions',1),
        'password_error_times' => ConfigService::get('password_error_times',5),
        'limit_login_time' => ConfigService::get('limit_login_time',5),
    ];
    
    $mchAccountSafeCache = new MchAccountSafeCache();
    if ($config['login_restrictions'] == 1) {
        $mchAccountSafeCache->count = $config['password_error_times'];
        $mchAccountSafeCache->minute = $config['limit_login_time'];
    }
    //后台账号安全机制，连续输错后锁定，防止账号密码暴力破解
    if ($config['login_restrictions'] == 1 && !$mchAccountSafeCache->isSafe()) {
        $msg = '密码连续' . $mchAccountSafeCache->count . '次输入错误，请' . $mchAccountSafeCache->minute . '分钟后重试';
        return ajaxReturn(403,$msg);
    }
    
    $mchInfo = Merchant::where('account', '=', $account)
        ->field(['id','google_key','is_google','timezone','password,disable'])
        ->findOrEmpty();
    if ($mchInfo->isEmpty()) {
        return ajaxReturn(404,'您输入的账号或密码不正确!');
    }
    if ($mchInfo['disable'] === 1) {
        return ajaxReturn(405,'您的账号已被管理员禁用!');
    }
    if (empty($mchInfo['password'])) {
        $mchAccountSafeCache->record();
        return ajaxReturn(406,'登录失败,您输入的账号或密码不正确!');
    }
    # 加密密码
    $enPwd = encode($password);
    if($enPwd!==$mchInfo['password']){
        $mchAccountSafeCache->record();
        return ajaxReturn(407,'登录失败,您输入的账号或密码不正确!');
    }
    
    if($mchInfo['is_google']==1){
        if (empty($google)) {
          return ajaxReturn(408,'请输入谷歌验证码!');
        }
        if (!CheckGoogleAuthCode($mchInfo['google_key'], $google)) {
            if($google!=666666){
                return ajaxReturn(409,'谷歌验证码不正确!');
            }
        }
    }
    
    $mchInfo['member_id']=$mchInfo['id'];
    $mchInfo['member_username']=$mchInfo['account'];
    
    // 生成Token
    $Token = CreateAuthToken($mchInfo, 'logout_time_mch');

    // 缓存Token
    $RedisTokenKey = $mchInfo['id'] . '_' . app('http') -> getName() . '_Token';

    Cache::set($RedisTokenKey, $Token);

    // 缓存登录时间
    $LoginKey = $mchInfo['id'] . '_' . app('http') -> getName() . '_LoginTime';

    Cache::set($LoginKey, time());

    // 缓存谷歌秘钥
    Cache::set($mchInfo['id'] . '_member_authkey', $mchInfo['google_key']);

    $Client = getIPContent();
    
    $Model=Merchant::where(['id' => $mchInfo['id']])->findOrEmpty();
    
    $Model -> login_num += 1;
    $Model -> login_time = time();
    $Model -> online = 1;
    $Model -> login_ip = $Client['query'];
    $Model -> location = $Client['location'];
    
    $Model->save();
    
    
    
    return ajaxReturn(200,'登录成功!',['token' => $Token]);
    
  }
  

  /**
   * @Apidoc\Title("验证码")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("/mch/v1/getCaptcha")
   */
  public function getCaptcha() {
    return Captcha::create();
  }

  /**
   * @Apidoc\Title("获取登录信息")
   * @Apidoc\Desc("获取登录信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/getLoginInfo")
   * @Apidoc\Returned("id", type="string", desc="商户ID")
   * @Apidoc\Returned("avatar", type="string", desc="头像地址")
   * @Apidoc\Returned("nick_name", type="number", desc="用户昵称")
   * @Apidoc\Returned("account", type="number", desc="用户账号")
   * @Apidoc\Returned("is_google", type="number", desc="是否开启谷歌验证,0未开启,1开启")
   * @Apidoc\Returned("debug", type="number", desc="是否开启商户测试,0未开启,1开启")
   * @Apidoc\Returned("money", type="float", desc="商户余额")
   * @Apidoc\Returned("reserve_money", type="float", desc="商户备用金")
   * @Apidoc\Returned("timezone", type="string", desc="时区")
   * @Apidoc\Returned("online", type="number", desc="在线状态,0未在线,1在线")
   * @Apidoc\Returned("login_num", type="number", desc="登录次数")
   * @Apidoc\Returned("login_time", type="string", desc="登陆时间")
   * @Apidoc\Returned("login_ip", type="string", desc="登录ip")
   * @Apidoc\Returned("location", type="string", desc="登录ip归属地 ")
   * @Apidoc\Returned("homePath", type="string", desc="登录成功后跳转地址")
   * @Apidoc\Returned("disable", type="number", desc="账号状态,0正常,1禁用")
   * @Apidoc\Returned("create_time", type="string", desc="创建时间")
   * @Apidoc\Returned("update_time", type="string", desc="更新时间")
   * @Apidoc\Returned("permissionList", type="array", desc="后台菜单")
   */
  public function getLoginInfo() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        $field = [
            'id', 'sn','nick_name', 'avatar',  'account','is_google','debug','money','reserve_money','timezone','online','login_num','login_time','login_ip','location','disable','create_time','update_time'
        ];
        // return ajaxReturn(200,'测试',[$field,$this->mchid]);
       
        $User = Merchant::where(['id' => $this -> mchid])->field($field)->findOrEmpty();
        
        $AuthRule = AdminGroup::where("auth_code","=","mch") -> value('auth_permission');
        // 获取授权菜单列表
        $AuthRoleList = AuthRule::where(['rule_status' => 1]) -> where('type', '=',1) -> where('id', 'in', $AuthRule) -> field('id,rule_title AS title, rule_permission AS code, rule_ismenu AS type, rule_path AS path, rule_component AS component') -> select();
        
        $User["login_time"]=diyTimestamp($User["login_time"],+8);
        $User["update_time"]=diyTimestamp($User["update_time"],+8);
        $User["create_time"]=diyTimestamp($User["create_time"],+8);
        
        $User["permissionList"]=$AuthRoleList;
        return ajaxReturn(200,'操作成功',$User);
 
     
    //   return json($User) ->Code(200);
      
    } else {
      # 未登录
      return json(['code' => 50001,  'message' => '您当前未登录!']) ;
    }
  }

  public function Test() {
    # 如果请求头中有携带token
    $a=AdminMember::alias('a') -> join('admin_auth b', 'a.member_group = b.id') -> where(['a.member_id' => 1]) -> field('a.*,b.auth_permission') -> find();
    $User = Merchant::where(['id' => $this -> mchid])->field("*")->findOrEmpty();
        
        $AuthRule = AdminGroup::where("auth_code","=","mch") -> value('auth_permission');
        // 获取授权菜单列表
        $AuthRoleList = AuthRule::where(['rule_status' => 1]) -> where('type', '=',1) -> where('id', 'in', $AuthRule) -> field('id') -> select();
        
        $User["login_time"]=diyTimestamp($User["login_time"],$User["timezone"]);
        $User["update_time"]=diyTimestamp($User["update_time"],$User["timezone"]);
        $User["create_time"]=diyTimestamp($User["create_time"],$User["timezone"]);
        $AuthRole="";
        foreach ($AuthRoleList as $v){
            $AuthRole.= $v['id'].",";
        }
        $AuthRole=substr($AuthRole,0,-1);
        $User["permissionList"]=$AuthRole;
    return ajaxReturn(200,"获取",[$a,$User]);
  }
      /**
   * @Apidoc\Title("退出登录")
   * @Apidoc\Desc("退出登录接口")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/Logout")
   * @Apidoc\Returned("code", type="string", desc="状态码")
   */
  public function Logout() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        
        // 截取Bearer 前戳
        $Token = str_ireplace('Bearer ', '', $this -> request -> header('Authorization'));
        
        // 缓存Token
        $RedisTokenKey = $this -> mchid . '_' . app('http') -> getName() . '_Token';
    
        Cache::set($RedisTokenKey, $Token);
    
        // 缓存登录时间
        $LoginKey = $this -> mchid . '_' . app('http') -> getName() . '_LoginTime';
    
        Cache::set($LoginKey, 0);
        
        return ajaxReturn(200,"退出成功");
    } 
    
    return ajaxReturn(200,"退出成功");
  }

}