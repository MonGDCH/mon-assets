<?php

require __DIR__ . '/../vendor/autoload.php';

use mon\env\Config;
use mon\assets\Asset;

Asset::instance()->init();

// 查询日志
// $option = [
//     'class'     => 'log',
//     'method'    => 'history',
//     'params'    => [
//         'uid'   => 1,
//         // 'name'  => 'ETH',
//         // 'source'=> 'test',
//         // 'from'  => 10,
//         // 'offset'=> 1,
//         // 'limit' => 5
//     ],
//     // 'id'        => 123456
// ];

// 记录日志
$option = [
    'class'     => 'log',
    'method'    => 'record',
    'params'    => [
        'uid'                => 1,
        'from'               => 1,
        'type'               => 99,
        'name'               => 'ETH',
        'source'             => 'composer excute',
        'available_before'   => '0',
        'available_num'      => '666',
        'available_after'    => '666',
        'freeze_before'      => '888',
        'freeze_num'         => '0',
        'freeze_after'       => '888',
    ],
];


// 查询资产
// $option = [
//     'class'     => 'balance',
//     'method'    => 'info',
//     'params'    => [
//         'uid'   => 1,
//         'names' => []
//     ],
// ];

// 扣减资产
// $option = [
//     'class'     => 'balance',
//     'method'    => 'deduction',
//     'params'    => [
//         'uid'   => 1,
//         'name'  => 'ETH',
//         'amount'=> '1',
//         'usable'=> '1'
//     ],
// ];

// 充值资产
// $option = [
//     'class'     => 'balance',
//     'method'    => 'charge',
//     'params'    => [
//         'uid'   => 1,
//         'name'  => 'BTC',
//         'amount'=> '5',
//         'usable'=> '0'
//     ],
// ];

// 可用资产冻结资产转换
// $option = [
//     'class'     => 'balance',
//     'method'    => 'shift',
//     'params'    => [
//         'uid'   => 1,
//         'name'  => 'ETH',
//         'amount'=> 1,
//         'usable'=> 0,
//         'source'=> 'test'
//     ],
// ];


// 资产兑换
// $option = [
//     'class'     => 'transform',
//     'method'    => 'conver',
//     'params'    => [
//         'uid'   => 1,
//         'from_name'  => 'ETH',
//         'from_amount'  => '1',
//         'from_usable'  => '0',
//         'to_name'  => 'BTC',
//         'to_amount'  => '1',
//         'to_usable'  => '1',
//     ],
// ];

// 资产转账
// $option = [
//     'class'     => 'transform',
//     'method'    => 'transfer',
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
// ];



$data = Asset::instance()->excute($option['class'], $option['method'], $option['params']);
if (!$data) {
    var_dump(Asset::instance()->getError());
    exit();
}
var_dump($data);
