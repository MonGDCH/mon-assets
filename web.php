<?php
/**
 * HTTP入口文件
 */
require __DIR__ . '/vendor/autoload.php';

// 初始化
\mon\assets\Asset::instance()->init();

// 获取参数
$option = $_POST;
// var_dump($option);exit();
// 验证参数
if (
    !isset($option['method']) || !isset($option['params']) || !isset($option['id'])
    || !is_string($option['method']) || empty($option['method'])
    || !is_array($option['params']) || empty($option['params'])
    || !is_numeric($option['id']) || !is_int($option['id'] + 0)
) {
    header("HTTP/1.1 400 Bad Request");
    exit();
}

// 执行
list($class, $method) = explode('.', $option['method']);
$data = \mon\assets\Asset::instance()->run($class, $method, $option['params']);
// 返回结果集
header("Content-Type: application/json");
echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit();
