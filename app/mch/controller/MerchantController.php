<?php

namespace app\mch\controller;

use app\model\AuthRule;
use think\facade\Cache;
use app\MchController;
use app\common\model\Merchant;
use think\captcha\facade\Captcha;
use hg\apidoc\annotation as Apidoc;
use app\mch\validate\MerchantValidate;
use think\exception\ValidateException;
use app\common\cache\MchAccountSafeCache;
use app\common\service\ConfigService;

/**
 * @Apidoc\Title("个人信息")
 * Author: JackMater
 */
class MerchantController extends MchController {
  
  /**
   * @Apidoc\Title("获取个人信息")
   * @Apidoc\Desc("获取个人信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/merchant/info")
   * @Apidoc\Returned("id", type="string", desc="商户ID")
   * @Apidoc\Returned("sn", type="string", desc="商户编号")
   * @Apidoc\Returned("secret_key", type="string", desc="商户密钥")
   * @Apidoc\Returned("avatar", type="string", desc="头像地址")
   * @Apidoc\Returned("nick_name", type="number", desc="用户昵称")
   * @Apidoc\Returned("account", type="number", desc="用户账号")
   * @Apidoc\Returned("is_google", type="number", desc="是否开启谷歌验证,0未开启,1开启")
   * @Apidoc\Returned("debug", type="number", desc="是否开启商户测试,0未开启,1开启")
   * @Apidoc\Returned("money", type="float", desc="商户余额")
   * @Apidoc\Returned("frozen_capital", type="float", desc="冻结资金")
   * @Apidoc\Returned("timezone", type="string", desc="时区")
   * @Apidoc\Returned("online", type="number", desc="在线状态,0未在线,1在线")
   * @Apidoc\Returned("login_num", type="number", desc="登录次数")
   * @Apidoc\Returned("login_time", type="string", desc="登陆时间")
   * @Apidoc\Returned("google_url", type="string", desc="谷歌验证码")
   * @Apidoc\Returned("login_ip", type="string", desc="登录ip")
   * @Apidoc\Returned("location", type="string", desc="登录ip归属地 ")
   * @Apidoc\Returned("homePath", type="string", desc="登录成功后跳转地址")
   * @Apidoc\Returned("disable", type="number", desc="账号状态,0正常,1禁用")
   * @Apidoc\Returned("pay_pwd", type="number", desc="支付密码,0未设置,1已设置")
   * @Apidoc\Returned("ip_white", type="array", desc="ip白名单")
   * @Apidoc\Returned("create_time", type="string", desc="创建时间")
   * @Apidoc\Returned("update_time", type="string", desc="更新时间")
   */
  public function info() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        $field = [
            'id', 'sn','nick_name', 'avatar',"frozen_capital",  'account','is_google','google_key','debug','money','timezone','online','login_num','login_time','login_ip','location','secret_key','disable','create_time','update_time','ip_white'
        ];
        // return ajaxReturn(200,'测试',[$field,$this->mchid]);
       
        $User = Merchant::where(['id' => $this -> mchid])->field($field)->findOrEmpty();
        
        
        
        
        if(empty($User->google_key)){
            $User->google_key=CreateGoogleAuthKey();
            $User->save();
        }
        $ggname=ConfigService::get('website_name','sifang').'--'.$User->account.'--'.$User->id;
        $url=CreateQrCodeImages($ggname, $User->google_key);
        
        $User['googleUrl']=$url;
        
        $User["login_time"]=diyTimestamp($User["login_time"],+8);
        $User["update_time"]=diyTimestamp($User["update_time"],+8);
        $User["create_time"]=diyTimestamp($User["create_time"],+8);
        $User["pay_pwd"]=$User["pay_pwd"]?1:0;
        
        
        return ajaxReturn(200,'操作成功',$User);
 
     
    //   return json($User) ->Code(200);
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }

  /**
   * @Apidoc\Title("修改个人资料")
   * @Apidoc\Desc("修改个人资料")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/merchant/update")
   * @Apidoc\Param("api_type", type="string", require=true, desc="接口类型:info:修改个人资料,debug,开启或关闭测试,key:重置api密钥,password:修改登录密码,upGoogle:修改谷歌密钥,closeGoogle:关闭谷歌验证码,bindGoogle:绑定谷歌验证码")
   * @Apidoc\Param("avatar", type="string", require=true, desc="头像地址")
   * @Apidoc\Param("nick_name", type="string", require=true, desc="用户昵称")
   * @Apidoc\Param("ip_white", type="array",  require=true,desc="ip白名单")
   * @Apidoc\Param("timezone", type="number", require=true, desc="时区")
   */
  public function update() {
    $prefix="merchantUser";
    $ip=getClientIP();
    $api_type=input("api_type")??"";
    $verify_code                =  input('verify_code')??""; # 获取验证码
    if ($this -> mchid) {
        addLog($prefix,1,'','');
        addLog($prefix,0,[$ip,$this -> mchid],'修改人信息');
        $merchant=Merchant::find($this -> mchid);
        switch ($api_type) {
            case 'info':
                // code...
                $params = (new MerchantValidate())->post()->goCheck('update');
                $newdata=isModification($params,$merchant);
                if(array_key_exists('newData',$newdata)){
                    $merchant->allowField(['avatar', 'nick_name','ip_white','timezone'])->save($newdata['newData']);
                    return messageReturn(200,"操作成功");
                }else{
                    return messageReturn(202,"数据相同,未作修改");
                }
                break;
            case 'debug':
                $debug=$merchant->debug==1?0:1;
                $merchant->debug=$debug;
                $merchant->save();
                
                return messageReturn(200,"操作成功",$merchant);
                break;
            case 'key':
                $salt=Merchant::createMerchantSalt();
                $secretKey=Merchant::secretKeyString($this -> mchid,$salt);
                $kdd=[
                    "salt"=>$salt,
                    "secret_key"=>$secretKey,
                ];
                $merchant->save($kdd);
                
                return ajaxReturn(200,"重置成功",$kdd);
                break;
            case 'password':
                $params = (new MerchantValidate())->post()->goCheck('password');
                addLog($prefix,0,$params,'接收的参数');
                 # 加密密码
                $enPwd = encode($params['password']);
                if($enPwd!==$merchant->password){
                    return messageReturn(4001,'原始密码不正确!');
                }
                $password=encode($params['password_new']);
                $merchant->save([
                    "password"=>$password
                ]);
                addLog($prefix,0,[$merchant],'修改完成');
                addLog($prefix,2,'','');
                return messageReturn(200,"操作成功");
                
            case 'upGoogle':
                 # 验证码为空
                if (empty($verify_code)) {
                    return messageReturn(4002,'请输入验证码!');
                }
                if(CheckGoogleAuthCode($merchant->google_key, $verify_code)){
                    $google_key=CreateGoogleAuthKey();
                    // $user->is_google=0;
                    $merchant->save([
                        "google_key"=>$google_key
                    ]);
                    $ggname=ConfigService::get('website_name','sifang').'--'.$merchant->account.'--'.$merchant->id;
                    $url=CreateQrCodeImages($ggname, $google_key);
                    return ajaxReturn(200,'操作成功',["url"=>$url]);
                    // return messageReturn(200,'操作成功');
                }else{
                    return messageReturn(500,'谷歌验证码错误!');
                }
                break;
            case 'closeGoogle':
                 # 验证码为空
                if (empty($verify_code)) {
                    return messageReturn(4001,'请输入验证码!');
                }
                if(CheckGoogleAuthCode($merchant->google_key, $verify_code)){
                    // $user->google_key=CreateGoogleAuthKey();
                    // $user->is_google=0;
                    $merchant->save([
                        "is_google"=>0
                    ]);
                    return messageReturn(200,'操作成功');
                }else{
                    return messageReturn(500,'谷歌验证码错误!');
                }
                break;
            case 'bindGoogle':
                if (empty($verify_code)) {
                    return ajaxReturn(4001,'请输入验证码!');
                }
                if(CheckGoogleAuthCode($merchant->google_key, $verify_code)){
                    $merchant->save([
                        "is_google"=>1
                    ]);
                    return messageReturn(200,'操作成功');
                }else{
                    return messageReturn(500,'谷歌验证码错误!');
                }
                break;
            
            default:
                // code...
                return messageReturn(500,"接口不存在");
                break;
        }
        try {
            
            $newdata=isModification($params,$merchant);
            
        } catch (\Exception $e) {
            return ajaxReturn(500,"系统错误",$e->getMessage());
        }
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
       
  }
  public function update1() {
        $params = (new MerchantValidate())->post()->goCheck('update');
        if($params['ip_white']!=""){
            $params['ip_white']=json_encode($params['ip_white']);
        }
        // return ajaxReturn(202,"测试",$params);
        try {
            $merchant=Merchant::find($this -> mchid);
            $newdata=isModification($params,$merchant);
            if(array_key_exists('newData',$newdata)){
                $merchant->save($newdata['newData']);
                return ajaxReturn(200,"操作成功");
            }else{
                return ajaxReturn(202,"数据相同,未作修改");
            }
        } catch (\Exception $e) {
            return ajaxReturn(500,"系统错误",$e->getMessage());
        }
  }
  
  /**
   * @Apidoc\Title("修改登录密码")
   * @Apidoc\Desc("修改登录密码")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/merchant/password")
   * @Apidoc\Param("password", type="string", require=true, desc="原密码")
   * @Apidoc\Param("password_new", type="string", require=true, desc="新密码")
   * @Apidoc\Param("password_confirm", type="string", require=true, desc="确认密码")
   */
  public function password() {
    $params = (new MerchantValidate())->post()->goCheck('password');
    try {
        $merchant=Merchant::find($this -> mchid);
        # 加密密码
        $enPwd = encode($params['password']);
        if($enPwd!==$merchant->password){
            return ajaxReturn(4001,'原始密码不正确!');
        }
        $merchant->password=encode($params['password_new']);
        $merchant->save();
        
        return ajaxReturn(200,"操作成功");
    } catch (\Exception $e) {
        return ajaxReturn(500,"系统错误",$e->getMessage());
    }
  }
  
  /**
   * @Apidoc\Title("修改支付密码(暂未使用)")
   * @Apidoc\Desc("修改支付密码")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/merchant/paypwd")pay_pwd
   * @Apidoc\Param("password", type="string", require=true, desc="登录密码")
   * @Apidoc\Param("pay_pwd", type="string", require=true, desc="支付密码")
   */
  public function paypwd() {
    
    return ajaxReturn(200,"开发中");
  }
  
  /**
   * @Apidoc\Title("获取谷歌验证码图片")
   * @Apidoc\Desc("获取谷歌验证码图片")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/merchant/googleImages")
   * @Apidoc\Returned("url", type="string", desc="图片地址")
   */
  public function googleImages() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
       
        $user = Merchant::where(['id' => $this -> mchid])->findOrEmpty();
        
        if(empty($user->google_key)){
            $user->google_key=CreateGoogleAuthKey();
            $user->save();
        }
        $ggname=ConfigService::get('website_name','sifang').'--'.$user->account.'--'.$user->id;
        $url=CreateQrCodeImages($ggname, $user->google_key);
        return ajaxReturn(200,'操作成功',["url"=>$url]);
      
    } else {
      # 未登录
      return ajaxReturn(50001,'您当前未登录');
    }
  }
  
  /**
   * @Apidoc\Title("绑定谷歌验证码")
   * @Apidoc\Desc("绑定谷歌验证码")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/merchant/bindGoogle")
   * @Apidoc\Param("verify_code", type="string",require=true, desc="谷歌验证码")
   */
  public function bindGoogle() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        $verify_code                =  input('verify_code'); # 获取验证码
         # 验证码为空
        if (empty($verify_code)) {
            return ajaxReturn(4001,'请输入验证码!');
        }
        
        $user = Merchant::where(['id' => $this -> mchid])->findOrEmpty();
        if(empty($user->google_key)){
            $user->google_key=CreateGoogleAuthKey();
        }
        if(CheckGoogleAuthCode($user->google_key, $verify_code)){
            $user->is_google=1;
            $user->save();
            return ajaxReturn(200,'操作成功');
        }else{
            return ajaxReturn(500,'谷歌验证码错误!');
        }
      
    } else {
      # 未登录
      return ajaxReturn(50001,'您当前未登录');
    }
  }
  
  /**
   * @Apidoc\Title("关闭谷歌验证码")
   * @Apidoc\Desc("关闭谷歌验证码")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/merchant/closeGoogle")
   * @Apidoc\Param("verify_code", type="string",require=true, desc="谷歌验证码")
   */
  public function closeGoogle() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        $verify_code                =  input('verify_code'); # 获取验证码
         # 验证码为空
        if (empty($verify_code)) {
            return ajaxReturn(4001,'请输入验证码!');
        }
        
        $user = Merchant::where(['id' => $this -> mchid])->findOrEmpty();
        
        if(CheckGoogleAuthCode($user->google_key, $verify_code)){
            $user->google_key=CreateGoogleAuthKey();
            $user->is_google=0;
            $user->save();
            return ajaxReturn(200,'操作成功');
        }else{
            return ajaxReturn(500,'谷歌验证码错误!');
        }
        
      
    } else {
      # 未登录
      return ajaxReturn(50001,'您当前未登录');
    }
  }

}