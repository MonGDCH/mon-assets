<?php
namespace mon\assets\command;

use Mon\console\Command;
use Mon\console\Input;
use Mon\console\Output;
use mon\orm\Db;
use mon\env\Config;
use mon\assets\Util;
use mon\orm\exception\MondbException;

class Install extends Command
{
    /**
     * 执行安装程序
     *
     * @param  Input  $in  输入实例
     * @param  Output $out 输出实例
     * @return int         exit状态码
     */
    public function execute(Input $in, Output $out)
    {
        $config = Config::instance()->get('mon_assets', []);
        if (empty($config)) {
            return $out->block('please configure the `mon_assets` for config.php', 'ERROR');
        }
        $out->write('install mon-assets setup is starting...');

        try {
            $total = 0;
            Db::setConfig($config['database']);
            Db::startTrans();
            // 安装资产表
            $tabname = $config['system']['assets']['name'];
            $tabtotal = $config['system']['assets']['total'];
            $total += Util::installAsstes($tabname, $tabtotal);
            // 安装日志表
            $tabname = $config['system']['log']['name'];
            $tabtotal = $config['system']['log']['total'];
            $total += Util::installLog($tabname, $tabtotal);

            Db::commit();
            $out->write('Total number of tables created:' . $total);
            return $out->write('installation complete, success!');
        } catch (MondbException $e) {
            Db::rollback();
            $out->write('MondbException: ');
            $out->write('File: ' . $e->getFile());
            $out->write('Line: ' . $e->getLine());
            return $out->write('Message: ' . $e->getMessage());
        } catch (Exception $e) {
            Db::rollback();
            $out->write('Exception: ');
            $out->write('File: ' . $e->getFile());
            $out->write('Line: ' . $e->getLine());
            return $out->write('Message: ' . $e->getMessage());
        }
    }
}
