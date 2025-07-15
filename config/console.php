<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        // 检测会员在线状态
        'CheckUserLoginTimeTask' => 'app\command\CheckUserLoginTimeTask',
        // 检测会员在线状态
        'OrderExpire' => 'app\command\OrderExpire',
        // 自动删除日志
        'LogFile' => 'app\command\LogFile',
    ],
];
