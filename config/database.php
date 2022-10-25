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

        //公会库
        'mysql_sociaty' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_SC'),
            'port' => env('DB_PORT_SC'),
            'database' => env('DB_DATABASE_SC'),
            'username' => env('DB_USERNAME_SC'),
            'password' => env('DB_PASSWORD_SC'),
            'charset' => env('DB_CHARSET_SC', 'utf8'),
        ],

        //活动库
        'mysql_activity' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_ACTIVITY'),
            'port' => env('DB_PORT_ACTIVITY'),
            'database' => env('DB_DATABASE_ACTIVITY'),
            'username' => env('DB_USERNAME_ACTIVITY'),
            'password' => env('DB_PASSWORD_ACTIVITY'),
            'charset' => env('DB_CHARSET_ACTIVITY', 'utf8mb4'),
        ],

        //竞猜库 霍克城堡
        'mysql_game' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_GAME'),
            'port' => env('DB_PORT_GAME'),
            'database' => env('DB_DATABASE_GAME'),
            'username' => env('DB_USERNAME_GAME'),
            'password' => env('DB_PASSWORD_GAME')
        ],

        //渠道推广系统
        'mysql_channel' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_CHANNEL'),
            'port' => env('DB_PORT_CHANNEL'),
            'database' => env('DB_DATABASE_CHANNEL'),
            'username' => env('DB_USERNAME_CHANNEL'),
            'password' => env('DB_PASSWORD_CHANNEL'),
            'charset'  => env('DB_CHARSET_CHANNEL', 'utf8mb4'),
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