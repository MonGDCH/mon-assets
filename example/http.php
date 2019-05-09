<?php

require __DIR__ . '/../vendor/autoload.php';

use mon\env\Config;
use mon\assets\Asset;

Asset::instance()->init();

// 查询日志
// $option = [
//     'method'    => 'log.history',
//     'params'    => [
//         'uid'   => 1,
//         // 'name'  => 'ETH',
//         // 'source'=> 'test',
//         // 'from'  => 10,
//         // 'offset'=> 1,
//         // 'limit' => 5
//     ],
//     'id'        => 123456
// ];

// 查询资产
$option = [
    'method'    => 'balance.info',
    'params'    => [
        'uid'   => 1,
        'names' => []
    ],
    'id'        => 123456
];

// 更新资产
// $option = [
//     'method'    => 'balance.update',
//     'params'    => [
//         'uid'   => 1,
//         'name'  => 'BTC',
//         'amount'=> 1,
//         'usable'=> 1,
//         'type'  => 1,
//     ],
//     'id'        => 123456
// ];

// 可用冻结转换
// $option = [
//     'method'    => 'balance.shift',
//     'params'    => [
//         'uid'   => 1,
//         'name'  => 'ETH',
//         'amount'=> 1,
//         'usable'=> 0,
//         'source'=> 'test'
//     ],
//     'id'        => 123456
// ];

// 兑换
// $option = [
//     'method'    => 'transform.conver',
//     'params'    => [
//         'uid'   => 1,
//         'from_name'  => 'ETH',
//         'from_amount'  => '1',
//         'from_usable'  => '0',
//         'to_name'  => 'BTC',
//         'to_amount'  => '1',
//         'to_usable'  => '1',
//     ],
//     'id'        => 123456
// ];

// 转账
// $option = [
//     'method'    => 'transform.transfer',
//     'params'    => [
//         'from_id'   => 1,
//         'to_id'   => 10,
//         'from_name'  => 'BTC',
//         'from_amount'  => '1',
//         'from_usable'  => '1',
//         'to_name'  => 'ETH',
//         'to_amount'  => '2',
//         'to_usable'  => '1',
//     ],
//     'id'        => 123456
// ];

list($class, $method) = explode('.', $option['method']);

$data = Asset::instance()->run($class, $method, $option['params']);

var_dump($data);

