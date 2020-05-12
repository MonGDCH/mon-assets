<?php
namespace mon\assets\log;

/**
 * 日志服务接口
 *
 * @version v1.0.0
 */
interface LogInterface
{
    /**
     * 记录日志
     *
     * @param  string $log  [description]
     * @param  string $type [description]
     * @return [type]       [description]
     */
    public function record($log, $type);
}
