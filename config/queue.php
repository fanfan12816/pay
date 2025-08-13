<?php
use think\facade\Env;

return [
    //默认使用redis数据库
    'default'     => 'redis',
    'prefix'      => env('cache.prefix', 'xy_'),
    'connections' => [
        // 同步执行
        'sync'     => [
            'driver' => 'sync',
        ],
        // 数据库驱动
        'database' => [
            'driver' => 'database',
            'queue'  => 'default',
            'table'  => 'jobs',
        ],
        // Redis驱动
        'redis'    => [
            //扩展名
            'type'     => 'redis',
            //默认队列的列名，可以进行修改
            'queue'      => 'XY'.env('cache.queue_name', 'xy_'),
            //redis的连接地址，一般读取env的配置
            'host'       => Env::get('cache.host', '127.0.0.1'),
            //redis的端口号，不用配置一般默认6379
            'port'       => Env::get('cache.port', 6379),
            //redis的密码，根据设置的进行配置
            'password'   => Env::get('cache.password', ''),
            //redis数据库选择的库，一般多项目部署，可以切换库。默认使用0
            'select'     => Env::get('cache.select', 0),
            //执行超时时长
            'timeout'    => 0,
            //是否持久化
            'persistent' => false,
        ],
    ],
    'failed'      => [
        'type'  => 'database',
        'table' => 'failed_jobs',
    ],
];
