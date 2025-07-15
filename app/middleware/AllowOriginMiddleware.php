<?php
declare (strict_types = 1);

namespace app\middleware;

use think\Request;
use think\Response;
use think\facade\Config;
use app\middleware\MiddlewareInterface;

class AllowOriginMiddleware implements MiddlewareInterface {

  /**
   * 允许跨域的域名
   * @var string
   */
  protected $cookieDomain;

  /**
   * @param Request $request
   * @param \Closure $next
   * @return Response
   */
  public function handle(Request $request, \Closure $next) {

    $response = $next($request);

    $this -> cookieDomain = Config::get('cookie.domain', '');
    $header = Config::get('cookie.header');
    $origin = $request -> header('origin');

    if ($origin && ('' == $this -> cookieDomain || strpos($origin, $this -> cookieDomain))) {
      $header['Access-Control-Allow-Origin'] = $origin;
    }
      
    if ($request -> method(true) == 'OPTIONS') {
      $response = Response::create('ok') -> code(200) -> header($header);
    } else {
      $response = $response -> header($header);
    }

    return $response;

  }

}