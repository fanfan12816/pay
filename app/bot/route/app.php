<?php

use think\facade\Route;
use app\middleware\AuthenticationToken;
use app\middleware\AllowOriginMiddleware;

  Route::any('/telegram', 'Telegram/index');
  Route::any('/index', 'Index/index');
Route::group('bot', function () {

  
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