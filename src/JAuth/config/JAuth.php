<?php

return [
    // token 键名
    'token_name' => 'token',

    // 过期时间
    'expiration' => 3600 * 24,

    'driver' => [
        'token' => [
            // 'database','redis'
            'storage_default' => 'database',
            'storage_config' => [
                'database' => [
                    // token存储数据表名
                    'ticket_table_name' => 'ticket'
                ],
                'redis' => [
                    'key_prefix' => 'lumen:'
                ]
            ],
        ],
        'session' => [
        ],
        'sso' => [
        ]
    ]
];
