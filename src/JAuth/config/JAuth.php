<?php

return [

    'table_names' => [
        // token存储数据表名
        'ticket' => 'ticket',
    ],

    // token 键名
    'token_name' => 'token',

    // 过期时间
    'expiration' => 3600 * 24,

    // 'database',
    'storage_driver' => 'database',

    'driver_config' => [
        'database' => '',
        'redis' => [
            'key_prefix' => 'lumen:'
        ]
    ],
];
