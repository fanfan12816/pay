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

  Route::any('/index', 'Index/index');

  # 获取广告列表
  Route::post('/getCommercialList', 'Commercial/getCommercialList');

  # 获取公告列表
  Route::post('/getPlacardList', 'Commercial/getPlacardList');

  # 轮播列表
  Route::post('/getSwipeList', 'Commercial/getSwipeList');

  # 获取站点信息
  Route::post('/getSystemConfig', 'WebConfig/getSystemConfig');

  # 前端登录
  Route::post('/LoginUser', 'Member/LoginUser');

  # 注册账户
  Route::post('/RegisterUser', 'Member/RegisterUser');

  # 验证码
  Route::any('/getCaptcha', 'Member/getCaptcha');

  # 钱包列表
  Route::post('/getWalletList', 'Wallet/getWalletList');

  # 获取语言列表
  Route::post('/getLangLocaleList', 'WebConfig/getLangLocaleList');

  # 上传APP启动日志
  Route::any('/CreateAppStaringLogs', 'AppStarting/CreateAppStaringLogs');

  # 获取活动信息
  Route::post('/getLuckyDrawInfo', 'LuckyDraw/getLuckyDrawInfo');

  # 获取活动奖品
  Route::post('/getLuckyDrawProductList', 'LuckyDraw/getLuckyDrawProductList');

  # 需要登录验证的接口
  Route::group(function () {
    
    # 获取用户信息
    Route::post('/getUserInfo', 'Member/getUserInfo');

    # 修改用户信息
    Route::post('/UpgradeUser', 'Member/UpgradeUser');

    # 注销账户
    Route::post('/ClearMemberInfo', 'Member/ClearMemberInfo');

    # 退出登录
    Route::post('/Logout', 'Member/Logout');

    # 文件上传
    Route::post('/UploadFile', 'UploadFileService/UploadFile');

    # 申请提现
    Route::post('/CreateWithdrawal', 'Wallet/CreateWithdrawal');

    # 提现记录
    Route::post('/WithdrawalOrder', 'Wallet/WithdrawalOrder');

    # 余额明细
    Route::post('/getBalanceRecords', 'Wallet/getBalanceRecords');

    # 实名认证
    Route::post('/CreateAuthUser', 'Member/CreateAuthUser');

    # 绑定银行卡
    Route::post('/CreateCardUser', 'Member/CreateCardUser');

    # 获取银行卡列表
    Route::post('/getBankCardList', 'Member/getBankCardList');

    # 删除已绑定的银行卡
    Route::post('/CannelUserCard', 'Member/CannelUserCard');

    # 充值
    Route::post('/CreateRechangeOrder', 'Wallet/CreateRechangeOrder');

    # 用户VIP等级
    Route::post('/getVipGrade', 'Member/getVipGrade');

    # 用户账户转账记录列表接口
    Route::post('/getTransferRecord', 'Member/getTransferRecord');

    # 用户财务记录列表
    Route::post('/getFinanceRecord', 'Member/getFinanceRecord');

    # 用户邀请链接信息
    Route::post('/getInviteLink', 'Member/getInviteLink');

    # 用户邀请中心数据信息
    Route::post('/getInviteInfo', 'Member/getInviteInfo');

    # 用户邀请下级用户列表
    Route::post('/getInviteList', 'Member/getInviteList');

    # 用户绑定上级接口
    Route::post('/bindingMember', 'Member/bindingMember');

    # 获取抽奖记录
    Route::post('/getLuckyDrawHistory', 'LuckyDraw/getLuckyDrawHistory');

    # 抽奖
    Route::post('/LuckyDrawAward', 'LuckyDraw/LuckyDrawAward');

  }) -> middleware(AuthenticationToken::class);
}) -> middleware(AllowOriginMiddleware::class);