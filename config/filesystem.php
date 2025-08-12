<?php

return [
    // 默认磁盘
    'default' => 'public',
    // 磁盘列表
    'disks'   => [
        'local'  => [
            'type' => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/UploadFile',
            // 磁盘路径对应的外部URL路径
            'url'        => '/UploadFile',
            // 可见性
            'visibility' => 'public',
        ],
        'public' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/UploadFile',
            // 磁盘路径对应的外部URL路径
            'url'        => '/UploadFile',
            // 可见性
            'visibility' => 'public',
        ],
        // 更多的磁盘配置信息
        'tencent' => [
            'type'         => 'tencent',
            'SecretId'     => env('TENCENT.SECRETID'),
            'SecretKey'    => env('TENCENT.SECRETKEY'),
            'bucket'       => env('TENCENT.BUCKET'),
            'endpoint'     => env('TENCENT.ENDPOINT'),
            'url'          => env('TENCENT.URL'),//不要斜杠结尾，此处为URL地址域名。
        ],
    ],
];
