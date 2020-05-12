<?php

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

function sendRquest($url,  $data = [], $type = 'post', $toJson = false, $timeOut = 0)
{
    // 判断是否为https请求
    $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, ($type == 'post') ? true : false);
    // 判断是否需要传递post数据
    if (count($data) != 0) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    // 设置内容以文本形式返回，而不直接返回
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($ssl) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
    }
    // 设置超时时间
    if (!empty($timeOut)) {
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
    }
    // 发起请求
    $html = curl_exec($ch);
    // 关于请求句柄
    curl_close($ch);
    $result = ($toJson) ? json_decode($html, true) : $html;
    return $result;
}

$url = 'http://127.0.0.1:8888';
$data = sendRquest($url, $option);

var_dump($data);

