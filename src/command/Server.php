<?php
namespace mon\assets\command;

use Mon\console\Command;
use Mon\console\Input;
use Mon\console\Output;

/**
 * 启动服务
 */
class Server extends Command
{
    /**
     * 执行指令
     *
     * @return [type] [description]
     */
    public function execute(Input $in, Output $out)
    {
        $out->write('start mon-assets server...');
        $out->write('format: php console mon-assets-server [ip:127.0.0.1] [port:8088]');


        $args = $in->getArgs();
        $ip = $args[0] ?? '127.0.0.1';
        $port = $args[1] ?? '8088';
        $root = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

        $command = sprintf(
            'php -S %s:%d -t %s %s',
            $ip,
            $port,
            escapeshellarg($root),
            escapeshellarg($root . DIRECTORY_SEPARATOR . 'web.php')
        );

        $out->write('server run start...');
        $out->write('You can exit with <info>`CTRL-C`</info>');
        $out->write(sprintf('Document root is: %s', $root));
        passthru($command);
    }
}
