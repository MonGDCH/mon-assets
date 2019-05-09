<?php
namespace mon\assets\log;

use mon\store\File;
use mon\env\Config;
use mon\assets\Asset;
use mon\assets\AssetException;
use mon\assets\log\LogInterface;

/**
 * 日志简单记录
 *
 * @version 1.0.0
 */
class Log implements LogInterface
{
    /**
     * 文件驱动
     *
     * @var [type]
     */
    protected $file;

    /**
     * 记录日志信息
     *
     * @param mixed  $msg       日志信息
     * @param string $type      日志级别
     * @return $this
     */
    public function record(string $msg, string $type = 'INFO')
    {
        if(!Asset::instance()->isHttp()){
            return true;
        }
        $now = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $log = "[{$now}] [{$type}] {$msg}".PHP_EOL;
        return $this->saveFile($log);
    }

    /**
     * 保存文件日志
     *
     * @param  [type] $content 内容
     * @return [type]          [description]
     */
    protected function saveFile($content)
    {
        if(!$this->file){
            $this->file = new File;
        }

        $dir = date('Ym', $_SERVER['REQUEST_TIME']);
        $file = date('Ymd', $_SERVER['REQUEST_TIME']);
        $base_path = Config::instance()->get('mon_assets.system.log_path');
        $path = $base_path . $dir . '/' . $file . '.log';

        $save = $this->file->createFile($content, $path);
        if(!$save){
            throw new AssetException("save http log faild", -4);
        }
        return $save;
    }
}