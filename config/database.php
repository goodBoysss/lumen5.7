<?php

/**
 * 数据库配置文件
 * panglishan
 * 2020.07.28
 */

return [

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        //主库
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => env('DB_CHARSET', 'utf8'),
        ],

    ],

    //redis配置
    'redis' => [

        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT'),
            'database' => env('REDIS_DATABASE'),
        ],
    ],

];