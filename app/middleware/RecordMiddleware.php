<?php

namespace app\middleware;

use think\Request;
use app\model\InterFacelog;
 
class RecordMiddleware {
  
  /**
   * @param Request $request
   * @param \Closure $next
   * @return Response
   */
  public function handle(Request $request, \Closure $next) {

    // 在请求处理之前做一些事情
    $response = $next($request); // 执行其他中间件

    // 在请求处理之后做一些事情
    $this -> RecordAccessLog($request, $response);
        
    return $response;

  }

  protected function RecordAccessLog($request, $response) {

    // 接口应用名
    $buid_type = app('http') -> getName();

    // 获取客户端IP信息
    $Client = getIPContent();

    // 参数
    $Params = [
      // 接口地址
      'interface_path'       => $request -> url(),
      // 接口应用名
      'interface_type'       => $buid_type,
      // 接口类型
      'interface_method'     => $request -> method(),
      // 请求头
      'interface_headers'    => json_encode($request -> header(), true),
      // 响应头
      'interface_re_header'  => json_encode($response -> getHeader(), true),
      // 请求参数
      'interface_params'     => json_encode($request -> param(), true),
      // 响应参数
      'interface_respones'   => '',
      // 客户端IP地址
      'interface_ip'         => $Client['query'],
      // 客户端IP属地
      'interface_city'       => $Client['location'],
    ];

    // 保存访问记录
    InterFacelog::create($Params);

  }

}