<?php
return [
    'controller_namespace'=>"App\\Controllers\\",
    'route'=>require(__DIR__ . '/route.php'),
    'components'=>[
        'db'=>[
            'class'=>'\JSwoole\Database\DB',
            'params'=>[
                'connection_config'=>[
                    'default'=>[
                        'host'        => env('DB_HOST', 'localhost'),
                        // 数据库名
                        'database'        => env('DB_DATABASE', ''),
                        // 用户名
                        'username'        => env('DB_USERNAME', 'root'),
                        // 密码
                        'password'        => env('DB_PASSWORD', ''),
                        // 端口
                        'port'        => env('DB_PORT', 3306),
                        // 数据库编码默认采用utf8
                        'charset'         => env('DB_CHARSET', 'utf8'),
                        // 数据库表前缀
                        'prefix'          => env('DB_PREFIX', ''),
                    ]
                ]
            ]
        ]
    ]
];