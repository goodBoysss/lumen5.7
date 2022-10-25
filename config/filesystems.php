<?php

return [


    'default' => env('FILESYSTEM_DRIVER', 'local'),

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('file'),
        ],

        //阿里云OSS配置
        'oss' => [
                'driver'        => 'oss',
                'access_id'     => env('OSS_ACCESSID'),
                'access_key'    => env('OSS_ACCESSKEY'),
                'bucket'        => env('OSS_BUCKET'),
                'endpoint'      => env('OSS_ENDPOINT'), // OSS 外网节点或自定义外部域名
                //'endpoint_internal' => '<internal endpoint [OSS内网节点] 如：oss-cn-shenzhen-internal.aliyuncs.com>', // v2.0.4 新增配置属性，如果为空，则默认使用 endpoint 配置(由于内网上传有点小问题未解决，请大家暂时不要使用内网节点上传，正在与阿里技术沟通中)
//                'cdnDomain'     => '<CDN domain, cdn域名>', // 如果isCName为true, getUrl会判断cdnDomain是否设定来决定返回的url，如果cdnDomain未设置，则使用endpoint来生成url，否则使用cdn
//                'ssl'           => <true|false> // true to use 'https://' and false to use 'http://'. default is false,
//              'isCName'       => <true|false> // 是否使用自定义域名,true: 则Storage.url()会使用自定义的cdn或域名生成文件url， false: 则使用外部节点生成url
//              'debug'         => <true|false>
        ],

    ],

];
