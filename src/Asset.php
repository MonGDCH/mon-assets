<?php

namespace mon\assets;

use mon\env\Config;
use mon\factory\Container;
use mon\assets\Util;
use mon\assets\AssetException;
use mon\assets\log\LogInterface;

class Asset
{
    /**
     * 单例实现
     *
     * @var Asset
     */
    protected static $instance;

    /**
     * 初始化标志
     *
     * @var boolean
     */
    protected $init = false;

    /**
     * 是否为HTTP调用
     *
     * @var boolean
     */
    protected $http = false;

    /**
     * 错误信息
     *
     * @var mixed
     */
    protected $error;

    /**
     * 获取单例
     *
     * @return Asset
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * 初始化
     *
     * @return void
     */
    public function init(array $config = [])
    {
        if (empty($config)) {
            $path = __DIR__ . '/../config/config.php';
            // 加载配置信息
            Config::instance()->load($path, 'mon_assets');
        } else {
            Config::instance()->set('mon_assets', $config);
        }

        $config = Config::instance()->get('mon_assets');
        if (!empty($config['system']['log_dirve'])) {
            $obj = new $config['system']['log_dirve']();
            if (!$obj instanceof LogInterface) {
                throw new AssetException("log dirve not instanceof for LogInterface", -3);
            }
            // 设置日志驱动
            Util::$log_drive = $obj;
        }

        $this->init = true;
    }

    /**
     * 执行应用HTTP
     *
     * @param  string $class  对象名
     * @param  string $method 方法
     * @param  array  $params 参数
     * @return array  结果集
     */
    public function run(string $class, string $method, array $params = [])
    {
        $this->http = true;
        $log = "class => {$class}, method => {$method}, params => " . var_export($params, true);
        Util::ossLog(__FILE__, __LINE__, $log);
        try {
            // 获取对应对象
            $class = '\\mon\\assets\\model\\' . ucfirst($class);
            if (!class_exists($class)) {
                return $this->res(-4, 'class not found');
            }
            $method = $method . 'Action';
            $object = Container::instance()->make($class);
            if (!method_exists($object, $method)) {
                return $this->res(-1, 'mothod not found');
            }
            // 执行类方法
            $data = call_user_func([$object, $method], $params);
            return $this->res(0, 'ok', $data);
        } catch (AssetException $e) {
            return $this->res($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 执行应用
     *
     * @param  string $command 模型名称
     * @param  string $method  调用方法
     * @param  array  $params  请求参数
     * @return mixed
     */
    public function excute(string $class, string $method, array $params = [])
    {
        try {
            // 获取对应对象
            $class = '\\mon\\assets\\model\\' . ucfirst($class);
            if (!class_exists($class)) {
                $this->error = 'class not found';
                return false;
            }

            $object = Container::instance()->make($class);
            if (!method_exists($object, $method)) {
                $this->error = 'mothod not found';
                return false;
            }
            // 执行类方法
            $data = call_user_func([$object, $method], $params);
            return $data;
        } catch (AssetException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 判断是否已初始化
     *
     * @return boolean
     */
    public function isInit()
    {
        return $this->init;
    }

    /**
     * 判断是否为HTTP调用
     *
     * @return boolean
     */
    public function isHttp()
    {
        return $this->http;
    }

    /**
     * 定义返回结果集
     *
     * @param integer $code 状态码
     * @param string $msg   信息
     * @param array $data   数据
     * @return array
     */
    public function res($code = 0, $msg = '', array $data = [])
    {
        return [
            'code'  => $code,
            'msg'   => $msg,
            'data'  => $data
        ];
    }

    /**
     * 获取错误信息
     *
     * @return mixed
     */
    public function getError()
    {
        $error = $this->error;
        $this->error = '';
        return $error;
    }
}
