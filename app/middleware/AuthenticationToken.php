<?php
declare (strict_types = 1);

namespace app\middleware;

use Exception;
use Throwable;
use app\model\Member;
use think\facade\Cache;
use app\common\Auth\{AuthService,MchAuthService};
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use app\middleware\MiddlewareInterface;
use Firebase\JWT\SignatureInvalidException;

/**
 * JWT验证刷新token机制
 * Class JWTToken
 * @package app\api\middleware
 */
class AuthenticationToken implements MiddlewareInterface {

  // 用户ID
  protected $member_id;
  
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
      return json(['code' => 401, 'data' => ['code' => 401, 'message' => '您当前未登录,请登录!']]);
    }

    // 截取Bearer 前戳
    $AuthToken = str_ireplace('Bearer ', '', $Authorization);

    // 当前账户余额
    $Balance = 0.00;

    try {
      // 校验Token成功
      $info = decode($AuthToken);

      // 保存用户ID
      $this -> member_id = $info -> member_id;

      // 扣减时间
      $time = intval($info -> exp) - time();

      $RedisKey = $this -> member_id . '_' . app('http') -> getName() . '_Token';

      // 获取缓存的Token
      $CacheToken = Cache::get($RedisKey);

      // 校验是否被强制踢下线
      if ($CacheToken !== $AuthToken) {
          if(app('http') -> getName() !== 'mch'){
            // 您的登录已失效
            return json(['code' => 401, 'data' => ['code' => 401, 'message' => '您的账号已在别处登录了!',[$info]]]);
          }
      }

      // 判断是前端还是后台
      if (app('http') -> getName() === 'api') {
        $config_key = 'logout_time_web';

        // 获取账户余额
        $Balance = Member::where(['member_id' => $this -> member_id]) -> value('member_balance');
        
      } elseif(app('http') -> getName() === 'mch'){
          $config_key = 'logout_time_mch';
          /**
             * 初始化后台接口权限验证服务
             */
            $AuthService = app(MchAuthService::class, ['member_id' => $this -> member_id]);
    
            // 校验接口权限
            $CheckAuthRule = $AuthService -> CheckAuth();
    
            // 验证权限
            if (!$CheckAuthRule) {
              return json(['code' => 0, 'data' => ['code' => 0, 'message' => '您没有访问权限!']]);
            }
      }else{
        $config_key = 'logout_time_admin';
        // $Model= Member::where(['member_id' => $this -> member_id]);
        // if($Model->isEmpty()){
        //   return json(['code' => 0, 'data' => ['code' => 0, 'message' => '账号已经被删除!']]);
        // }
        // if($Model->member_status!=1){
        //   return json(['code' => 0, 'data' => ['code' => 0, 'message' => '您的账号已被管理员禁用!']]);
        // }
        /**
         * 初始化后台接口权限验证服务
         */
        $AuthService = app(AuthService::class, ['member_id' => $this -> member_id]);
        // 检验账号
        $adminInfo=$AuthService->CheckAdmin();
        if(empty($adminInfo)){
          return json(['code' => 0, 'data' => ['code' => 0, 'message' => '账号已经被删除!']]);
        }
        if($adminInfo['member_status']!=1){
          return json(['code' => 0, 'data' => ['code' => 0, 'message' => '您的账号已被管理员禁用!']]);
        }
        // 校验接口权限
        $CheckAuthRule = $AuthService -> CheckAuth();

        // 验证权限
        if (!$CheckAuthRule) {
          return json(['code' => 0, 'data' => ['code' => 0, 'message' => '您没有访问权限!']]);
        }
      }

      // 校验Token过期时间
      if ($time < 600 && $time > 0) {
        // 生成新Token
        $token = CreateAuthToken($info, $config_key);
      }

    } catch (SignatureInvalidException $e) {
      // 签名校验失败
      return json(['code' => 401, 'data' => ['code' => 401, 'message' => '签名验证失败!']]);
    } catch (BeforeValidException $e) {
      // 签名在某个时间点之后才能用
      return json(['code' => 401, 'data' => ['code' => 401, 'message' => 'Token暂未生效!']]);
    } catch (ExpiredException $e) {
      // token过期
      return json(['code' => 401, 'data' => ['code' => 401, 'message' => 'token过期,请重新登录!']]);
    } catch (Exception $e) {
      // 其他错误
      return json(['code' => 401, 'data' => ['code' => 4010, 'message' => '登录失效,请重新登录!','data'=>$e]]);
    } catch (Throwable $e) {
      // 其他错误
      return json(['code' => 401, 'data' => ['code' => 4011, 'message' => '登录失效,请重新登录!','data'=>$e]]);
    }

    $response = $next($request);
 
    // 如果有新的token，则在响应头返回（前端判断一下响应中是否有 token，如果有就直接使用此 token 替换掉本地的 token，以此达到无痛刷新token效果）
    if (isset($token)) {
      $response -> header(['Authorization' => 'Bearer ' . $token]);
    }

    // 将账户余额写入前端接口响应头
    if (app('http') -> getName() === 'api') {
      $response -> header(['member_balance' => $Balance]);
    }

    return $response;
  }
}