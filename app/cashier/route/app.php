<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;
use app\middleware\AuthenticationToken;
use app\middleware\AllowOriginMiddleware;

Route::any('/index', 'Index/index');
Route::group('v1', function () {

  
  Route::group('pay', function () {
      # 获取订单详情
      Route::get('/info', 'Pay/info');
      # 上传图片
      Route::post('/upload', 'Pay/upload');
      # 提交数据
      Route::post('/submit', 'Pay/submit');
      # 提交数据
      Route::post('/submitTest', 'Pay/submitTest');
  });


}) -> middleware(AllowOriginMiddleware::class);