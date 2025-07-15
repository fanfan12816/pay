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
  Route::post('/LoginUser', 'Login/LoginUser');

  # 退出登录
  Route::post('/Logout', 'Login/Logout');

  # 验证码
  Route::any('/getCaptcha', 'Login/getCaptcha');
  
  
  #获取站点信息
  Route::get('/webSite', 'SystemConfig/webSite');

  # 需要登录验证的接口
  Route::group(function () {
    
    # 商户管理
    Route::group('merchant', function () {
        
        # 获商户列表
        Route::get('/lists', 'merchant.Merchant/lists');
        # 导出
        Route::get('/inExport', 'merchant.Merchant/inExport');
        # 新增商户
        Route::post('/add', 'merchant.Merchant/add');
        # 编辑商户
        Route::post('/edit', 'merchant.Merchant/edit');
        # 余额操作
        Route::post('/money', 'merchant.Merchant/money');
        # 删除商户
        Route::post('/del', 'merchant.Merchant/del');
    });
    # 分析
    Route::group('dataInfo', function () {
        # 代收
        Route::get('/index', 'dataInfo.DataInfo/index');
        # 代收
        Route::get('/payin', 'dataInfo.DataInfo/payin');
        # 代收
        Route::get('/payinExport', 'dataInfo.DataInfo/payinExport');
         # 代付
        Route::get('/payout', 'dataInfo.DataInfo/payout');
        Route::get('/payoutExport', 'dataInfo.DataInfo/payoutExport');
         # 充值
        Route::get('/recharge', 'dataInfo.DataInfo/recharge');
        Route::get('/rechargeExport', 'dataInfo.DataInfo/rechargeExport');
         # 提现
        Route::get('/withdraw', 'dataInfo.DataInfo/withdraw');
        Route::get('/withdrawExport', 'dataInfo.DataInfo/withdrawExport');
    });
    # 系统管理
    Route::group('system', function () {
         # 机器人群设置
        Route::group('bot', function () {
            # 列表
            Route::get('/lists', 'system.BotGroup/lists');
            # 导出
            Route::get('/inExport', 'system.BotGroup/inExport');
            # 新增
            Route::post('/add', 'system.BotGroup/add');
            # 编辑
            Route::post('/edit', 'system.BotGroup/edit');
            # 配置
            Route::post('/config', 'system.BotGroup/config');
            # 删除
            Route::post('/del', 'system.BotGroup/del');
        });
         # 收银台语言
        Route::group('lang', function () {
            Route::get('/theme', 'system.Language/theme');
            # 列表
            Route::get('/lists', 'system.Language/lists');
            # 导出
            Route::get('/inExport', 'system.Language/inExport');
            # 编辑
            Route::post('/edit', 'system.Language/edit');
        });
    });
    # 商户管理
    Route::group('channel', function () {
        
        # 列表
        Route::get('/lists', 'channel.Channel/lists');
        # 导出
        Route::get('/inExport', 'channel.Channel/inExport');
        # 新增
        Route::post('/add', 'channel.Channel/add');
        # 编辑
        Route::post('/edit', 'channel.Channel/edit');
        # 分配
        Route::post('/allot', 'channel.Channel/allot');
        # 删除
        Route::post('/del', 'channel.Channel/del');
         # 商户通道
        Route::group('merchant', function () {
            # 列表
            Route::get('/lists', 'channel.MerchantChannel/lists');
            # 导出
            Route::get('/inExport', 'channel.MerchantChannel/inExport');
            # 编辑
            Route::post('/edit', 'channel.MerchantChannel/edit');
            # 删除
            Route::post('/del', 'channel.MerchantChannel/del');
        });
         # 商户通道
        Route::group('bank', function () {
            # 列表
            Route::get('/lists', 'channel.Bank/lists');
            # 导出
            Route::get('/inExport', 'channel.Bank/inExport');
            # 新增
            Route::post('/add', 'channel.Bank/add');
            # 编辑
            Route::post('/edit', 'channel.Bank/edit');
            # 删除
            Route::post('/del', 'channel.Bank/del');
        });
    });
    # 商户管理
    Route::group('order', function () {
        # 代收
        Route::group('payin', function () {
            # 列表
            Route::get('/lists', 'order.Payin/lists');
            # 导出
            Route::get('/inExport', 'order.Payin/inExport');
            # 回调
            Route::post('/callback', 'order.Payin/callback');
            # 通知
            Route::post('/notifier', 'order.Payin/notifier');
            # 关闭
            Route::post('/close', 'order.Payin/close');
            # 删除
            Route::post('/del', 'order.Payin/del');
        });
        # 代付
        Route::group('payout', function () {
            # 列表
            Route::get('/lists', 'order.Payout/lists');
            # 导出
            Route::get('/inExport', 'order.Payout/inExport');
            # 回调
            Route::post('/callback', 'order.Payout/callback');
            # 通知
            Route::post('/notifier', 'order.Payout/notifier');
            # 关闭
            Route::post('/close', 'order.Payout/close');
            # 删除
            Route::post('/del', 'order.Payout/del');
        });
        # 充值
        Route::group('recharge', function () {
            # 列表
            Route::get('/lists', 'order.Recharge/lists');
            # 导出
            Route::get('/inExport', 'order.Recharge/inExport');
            # 审核
            Route::post('/check', 'order.Recharge/check');
            # 删除
            Route::post('/del', 'order.Recharge/del');
        });
        # 提现
        Route::group('withdraw', function () {
            # 列表
            Route::get('/lists', 'order.Withdraw/lists');
            # 导出
            Route::get('/inExport', 'order.Withdraw/inExport');
            # 审核
            Route::post('/check', 'order.Withdraw/check');
            # 删除
            Route::post('/del', 'order.Withdraw/del');
        });
        # 流水
        Route::group('accountLog', function () {
            # 列表
            Route::get('/lists', 'order.AccountLog/lists');
            # 导出
            Route::get('/inExport', 'order.AccountLog/inExport');
        });
    });

    # 获取用户信息
    Route::post('/getUserInfo', 'Login/getUserInfo');

    # 站点信息列表
    Route::post('/getWebSiteConfig', 'WebConfig/getWebSiteConfig');

    # 更新站点信息
    Route::put('/UpgradeSiteConfig', 'WebConfig/UpgradeSiteConfig');

    # 新增站点
    Route::put('/AddSiteConfig', 'WebConfig/AddSiteConfig');

    # 删除站点
    Route::delete('/DeleteSiteConfig', 'WebConfig/DeleteSiteConfig');

    # 获取管理员账号列表
    Route::post('/getAdminMemberList', 'AdminMember/getAdminMemberList');

    # 修改管理员信息
    Route::put('/UpgradeAdminMember', 'AdminMember/UpgradeAdminMember');

    # 新增后台管理员
    Route::put('/AddAdminMember', 'AdminMember/AddAdminMember');

    # 删除管理员账户
    Route::delete('/DeleteAdminMember', 'AdminMember/DeleteAdminMember');

    # 文件上传
    Route::post('/UploadFile', 'UploadFileService/UploadFile');

    # 创建谷歌验证码秘钥
    Route::put('/CreateGoogleKey', 'AdminMember/CreateGoogleKey');

    # 获取菜单列表
    Route::post('/getAuthRuleList', 'AuthRule/getAuthRuleList');

    # 修改菜单
    Route::put('/UpgradeAuthRule', 'AuthRule/UpgradeAuthRule');

    # 新增菜单
    Route::put('/AddAuthRule', 'AuthRule/AddAuthRule');

    # 删除菜单
    Route::delete('/DeleteAuthRule', 'AuthRule/DeleteAuthRule');

    # 获取授权树形菜单
    Route::post('/getMenuList', 'AuthRule/getMenuList');

    # 获取授权菜单ID
    Route::post('/getPermCode', 'AuthRule/getPermCode');

    # 获取管理员用户组列表
    Route::post('/getAdminGroupList', 'AdminGroup/getAdminGroupList');

    # 修改管理员用户组
    Route::put('/UpgradeAdminGroup', 'AdminGroup/UpgradeAdminGroup');

    # 新增管理员用户组
    Route::put('/AddAdminGroup', 'AdminGroup/AddAdminGroup');

    # 删除管理员用户组
    Route::delete('/DeleteAdminGroup', 'AdminGroup/DeleteAdminGroup');

    # 获取系统配置列表
    Route::post('/getSystemConfigList', 'SystemConfig/getSystemConfigList');

    # 修改系统配置
    Route::put('/UpgradeSystemConfigList', 'SystemConfig/UpgradeSystemConfigList');

    # 新增系统配置
    Route::put('/AddSystemConfigList', 'SystemConfig/AddSystemConfigList');

    # 删除系统配置
    Route::delete('/DeleteSystemConfigList', 'SystemConfig/DeleteSystemConfigList');

    # 广告管理列表
    Route::post('/getCommercial', 'Commercial/getCommercial');

    # 更新广告
    Route::put('/UpgradeCommercial', 'Commercial/UpgradeCommercial');

    # 新增广告
    Route::put('/AddCommercial', 'Commercial/AddCommercial');

    # 删除广告
    Route::delete('/DeleteCommercial', 'Commercial/DeleteCommercial');

    # 轮播列表
    Route::post('/getSwipeList', 'Swipe/getSwipeList');

    # 更新轮播
    Route::put('/UpgradeSwipe', 'Swipe/UpgradeSwipe');

    # 新增轮播
    Route::put('/AddSwipe', 'Swipe/AddSwipe');

    # 删除轮播
    Route::delete('/DeleteSwipe', 'Swipe/DeleteSwipe');

    # 获取搜公告列表
    Route::post('/getPlacardList', 'Placard/getPlacardList');

    # 修改公告
    Route::put('/UpgradePlacard', 'Placard/UpgradePlacard');

    # 新增公告
    Route::put('/AddPlacard', 'Placard/AddPlacard');

    # 删除公告
    Route::delete('/DeletePlacard', 'Placard/DeletePlacard');

    # 获取客服列表
    Route::post('/getCoustemServerList', 'CoustemServer/getCoustemServerList');

    # 更新客服
    Route::put('/UpgradeCoustemServer', 'CoustemServer/UpgradeCoustemServer');

    # 新增客服
    Route::put('/AddCoustemServer', 'CoustemServer/AddCoustemServer');

    # 删除客服
    Route::delete('/DeleteCoustemServer', 'CoustemServer/DeleteCoustemServer');
    
    # 语言类型列表
    Route::post('/getLangTypeList', 'LangType/getLangTypeList');

    # 修改语言类型
    Route::put('/UpgradeLangType', 'LangType/UpgradeLangType');

    # 新增语言类型
    Route::put('/CreateLangType', 'LangType/CreateLangType');

    # 删除语言类型
    Route::delete('/DeleteLangType', 'LangType/DeleteLangType');

    # 接口日志列表
    Route::post('/getInerFaceLogList', 'InterFace/getInerFaceLogList');

    # 余额日志
    Route::post('/getFinancialRecords', 'InterFace/getFinancialRecords');

    # 国家列表
    Route::post('/getCountryList', 'Country/getCountryList');

    # 修改国家
    Route::put('/UpgradeCountry', 'Country/UpgradeCountry');

    # 新增国家
    Route::put('/AddCountry', 'Country/AddCountry');

    # 删除国家
    Route::delete('/DeleteCountry', 'Country/DeleteCountry');

    # 获取数据库列表
    Route::post('/getDataBaseList', 'SystemDatabase/getDataBaseList');

    # 获取数据表详情
    Route::post('/getDataBaseInfo', 'SystemDatabase/getDataBaseInfo');

    # 优化数据库表
    Route::post('/UpdateOptimize', 'SystemDatabase/UpdateOptimize');

    # 修复数据库表
    Route::post('/RepairDataBase', 'SystemDatabase/RepairDataBase');

    # 备份数据库
    Route::put('/BackUpDataBase', 'SystemDatabase/BackUpDataBase');

    # 备份记录
    Route::post('/getBackupFileList', 'SystemDatabase/getBackupFileList');

    # 删除备份
    Route::delete('/DeleteBackupFileList', 'SystemDatabase/DeleteBackupFileList');

  }) -> middleware(AuthenticationToken::class);

}) -> middleware(AllowOriginMiddleware::class);