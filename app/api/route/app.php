<?php

use think\facade\Route;
use app\middleware\ApiToken;
use app\middleware\AllowOriginMiddleware;

Route::any('/index', 'Index/index');
Route::group('auto', function () {
        Route::post('/payin', 'auto.Auto/payin');
        Route::post('/callback', 'auto.Auto/callback');
        Route::post('/failback', 'auto.Auto/failback');
        
    });
Route::group('v1', function () {
    Route::any('/index', 'Index/index');
    # 需要登录验证的接口
    Route::group(function () {
    # 下单接口
    Route::group('payin', function () {
        # 代收下单
        Route::post('/transactions', 'orders.Payin/transactions');
        
    });
    Route::group('payout', function () {
        # 代付批量下单
        // Route::get('/bulkOrders', 'orders.Payout/bulkOrders');
        Route::post('/bulkOrders', 'orders.Payout/bulkOrders');
        # 代收下单
        Route::post('/transactions', 'orders.Payout/transactions');
        # 代收下单
        Route::post('/banklists', 'orders.Payout/banklists');
        
    });
    Route::group('query', function () {
        # 商户信息查询接口
        Route::post('/merchant', 'query.Query/merchant');
        # 商户信息查询接口
        Route::post('/channel', 'query.Query/channel');
        # 代收订单查询接口
        Route::post('/payin', 'query.Query/payin');
        # 代付订单查询接口
        Route::post('/payout', 'query.Query/payout');
        
    });
    
    }) -> middleware(ApiToken::class);
}) -> middleware(AllowOriginMiddleware::class);