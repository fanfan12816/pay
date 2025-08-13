<?php
declare (strict_types = 1);

namespace app\middleware;

use Exception;
use Throwable;
use app\common\model\Merchant;
use app\middleware\MiddlewareInterface;

/**
 * JWT验证刷新token机制
 * Class JWTToken
 * @package app\api\middleware
 */
class ApiToken implements MiddlewareInterface {

  // 商户详情
  protected $merchantInfo;
  
  /**
   * 刷新token
   * @param $request
   * @param \Closure $next
   * @return mixed
   * @throws JWTException
   * @throws TokenBlacklistException
   * @throws TokenBlacklistGracePeriodException
   */
  public function handle($request, \Closure $next): object {

    $Authorization = $request -> header('Authorization');

    // 如果没携带token
    if (empty($Authorization)) {
      return ajaxReturn(5001,'接口认证失败',$Authorization);
    }
    //   return ajaxReturn(40001,'非法请求',$Authorization);

    // 截取Bearer 前戳
    // $AuthToken = str_ireplace('Bearer ', '', $Authorization);

    // 当前账户余额
    $merchantInfo = [];

    try {
        $field = [
            'id', 'sn','nick_name',"frozen_capital",'account','debug','money','timezone','secret_key','disable','ip_white'
        ];
        // return ajaxReturn(200,'测试',[$field,$this->mchid]);
       
        $User = Merchant::where(['secret_key' => $Authorization])->field($field)->findOrEmpty();
        
        if($User->isEmpty()){
            return messageReturn(5004,'商户密钥有误');
        }
        
        $User['ip_white'].=",127.0.0.1";
        
        if(!empty($User['ip_white'])){
            $User['ip_white']=explode(',', $User['ip_white']);
        }else{
            $User['ip_white']=[];
        }
        $isCheck=MchCheckIP($User['ip_white']);
        if(!$isCheck){
            $prefix="ipWhiteError";
            @$rt=[
                "getIp"=>getClientIP(),
                "user_ip"=>$User['ip_white']??"",
                "token"=>$Authorization??"",
                "mch"=>$User['id']??""
                
            ];
            addLog($prefix,0,[$rt],'ip错误信息');
            
            return ajaxReturn(5002,'ip不在白名单里面,请在商户后台设置',$rt);
        }
       
        if($User['disable']==1){
           return ajaxReturn(5003,'账号已经被锁定,请联系客服处理');
        }
        

    } catch (Exception $e) {
      return ajaxReturn(5000,'接口错误',$e);
    } 

    $response = $next($request);

    return $response;
  }
}