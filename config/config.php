<?php

/*
|--------------------------------------------------------------------------
| 配置文件
|--------------------------------------------------------------------------
| 定义应用配置信息
|
*/
return [
    // 资产类型配置, key为资产名称，值为附加信息, 按需配置
    'balance'   => [
        'BTC'   => [
            'prec'  => 8
        ],
        'ETH'   => [
            'prec'  => 4
        ],
    ],
    // 数据库配置
    'database'  => [
        // 数据库类型
        'type'            => 'mysql',
        // 服务器地址
        'host'            => '127.0.0.1',
        // 数据库名
        'database'        => 'asset',
        // 用户名
        'username'        => 'root',
        // 密码
        'password'        => 'root',
        // 端口
        'port'            => '3306',
        // 数据库编码默认采用utf8
        'charset'         => 'utf8',
        // 返回结果集类型
        'result_type'     => PDO::FETCH_ASSOC,
    ],
    // 系统配置
    'system'    => [
        // 资产表名，及分表数, 应用安装启动后请勿改动
        'assets'    => [
            'name'  => 'asset_balance',
            'total' => 10
        ],
        // 资产流水表名，及分表数, 应用安装启动后请勿改动
        'log'       => [
            'name'  => 'asset_balance_log',
            'total' => 10
        ],
        // SQL日志记录驱动类名称，需实现\mon\assets\log\LogInterface接口, 空则不使用
        // 注： mon\assets\log\Log为内置HTTP调用下使用的日志驱动, 可自行替换日志驱动
        'log_dirve' => mon\assets\log\Log::class,
        // 日志文件保存路径
        'log_path'  => __DIR__ . '/../log/',
    ],
];