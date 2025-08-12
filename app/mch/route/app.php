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

Route::group('v1', function () {

  # 登录
  Route::post('/Login', 'Login/Login');

  # 退出登录
  Route::post('/Logout', 'Login/Logout');

  # 验证码
  Route::any('/getCaptcha', 'Login/getCaptcha');
  
  #系统信息
  Route::group("system",function () {
      # 获取站点信息
      Route::post('/webSite', 'System/webSite');
     
  });

  # 需要登录验证的接口
  Route::group(function () {
    # 退出登录
  Route::post('/Test', 'Login/Test');
    # 获取用户信息
    Route::post('/getLoginInfo', 'Login/getLoginInfo');
    #商户信息
    Route::group("merchant",function () {
        # 获取个人信息
        Route::post('/info', 'Merchant/info');
        # 修改个人信息
        Route::post('/update', 'Merchant/update');
        # 修改登录密码
        Route::post('/password', 'Merchant/password');
        # 修改支付密码
        Route::post('/paypwd', 'Merchant/paypwd');
        # 获取个人信息
        Route::post('/info', 'Merchant/info');
        # 获取谷歌验证码图片
        Route::post('/googleImages', 'Merchant/googleImages');
        # 绑定谷歌验证码
        Route::post('/bindGoogle', 'Merchant/bindGoogle');
        # 关闭谷歌验证码
        Route::post('/closeGoogle', 'Merchant/closeGoogle');
    });
    #系统信息
    Route::group("system",function () {
        # 获取首页数据
        Route::post('/indexData', 'System/indexData');
        # 获取机器人配置
        Route::get('/botLists', 'System/botLists');
        # 修改机器人信息
        Route::post('/updateBot', 'System/updateBot');
        # 修改机器人语言
        Route::post('/updateBotExtra', 'System/updateBotExtra');
        # 新增机器人
        Route::post('/addBot', 'System/addBot');
        # 删除机器人
        Route::post('/delBot', 'System/delBot');
        # 获取多语言配置
        Route::get('/language', 'System/language');
        # 修改多语言配置
        Route::post('/upLanguage', 'System/upLanguage');
       
    });
    #订单管理
    Route::group("order",function () {
        # 代收回调
        Route::post('/payinCallback', 'Order/payinCallback');
        # 代收通知
        Route::post('/payinNotifier', 'Order/payinNotifier');
        # 代付回调
        Route::post('/payoutCallback', 'Order/payoutCallback');
        # 代付通知
        Route::post('/payoutNotifier', 'Order/payoutNotifier');
        # 关闭提现
        Route::post('/withdrewClose', 'Order/withdrewClose');
        # 关闭代收
        Route::post('/payinClose', 'Order/payinClose');
        # 关闭代付
        Route::post('/payoutClose', 'Order/payoutClose');
        # 充值
        Route::post('/addRecharge', 'Order/addRecharge');
        # 提现
        Route::post('/addWithdraw', 'Order/addWithdraw');
        # 代收下单
        Route::post('/checkin', 'Order/checkin');
        # 代付下单
        Route::post('/checkout', 'Order/checkout');
        # 获取测试订单代收列表
        Route::get('/testPayinLists', 'Order/testPayinLists');
        # 获取测试订单代付列表
        Route::get('/testPayoutLists', 'Order/testPayoutLists');
        # 获取代收列表
        Route::get('/payinLists', 'Order/payinLists');
        # 获取代收导出
        Route::get('/payinExport', 'Order/payinExport');
        # 获取代付列表
        Route::get('/payoutLists', 'Order/payoutLists');
        # 获取代付导出
        Route::get('/payoutExport', 'Order/payoutExport');
        # 获取充值订单列表
        Route::get('/rechargeLists', 'Order/rechargeLists');
        # 获取充值订单导出
        Route::get('/rechargeExport', 'Order/rechargeExport');
        # 获取提现订单列表
        Route::get('/withdrawLists', 'Order/withdrawLists');
        # 获取提现订单导出
        Route::get('/withdrawExport', 'Order/withdrawExport');
        # 获取流水明细列表
        Route::get('/accountLogLists', 'Order/accountLogLists');
        # 获取流水明细导出
        Route::get('/accountLogExport', 'Order/accountLogExport');
        # 获取通道列表
        Route::get('/channelLists', 'Order/channelLists');
        # 代收银行卡列表
        Route::get('/payinBankLists', 'Order/payinBankLists');
        # 代付银行卡列表
        Route::get('/payoutBankLists', 'Order/payoutBankLists');
        
       
    });
    
    # 获取授权树形菜单
    Route::post('/getMenuList', 'AuthRule/getMenuList');

    # 获取授权菜单ID
    Route::post('/getPermCode', 'AuthRule/getPermCode');

    # 文件上传
    Route::post('/UploadFile', 'UploadFileService/UploadFile');


  }) -> middleware(AuthenticationToken::class);

}) -> middleware(AllowOriginMiddleware::class);