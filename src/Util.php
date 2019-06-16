<?php
namespace mon\assets;

use mon\orm\Db;

/**
 * 工具类
 */
class Util
{
    /**
     * 日志驱动
     *
     * @var [type]
     */
    public static $log_drive;

    /**
     * 记录日志
     *
     * @param  [type] $file 文件
     * @param  [type] $line 行
     * @param  [type] $log  日志信息
     * @param  string $type 类型
     * @return [type]       [description]
     */
    public static function ossLog($file, $line, $log, $type = 'INFO')
    {
        if (is_null(self::$log_drive)) {
            return true;
        }

        $message = "[{$file} => {$line}] " . $log;
        return self::$log_drive->record($message, $type);
    }

    /**
     * 安装资产表
     *
     * @param  [type] $tabname  [description]
     * @param  [type] $tabtotal [description]
     * @return [type]           [description]
     */
    public static function installAsstes($tabname, $tabtotal, $echo = true)
    {
        $total = 0;
        if ($tabtotal > 1) {
            for ($i = 0; $i < $tabtotal; $i++) {
                $name = $tabname . '_' . $i;
                if ($echo) {
                    echo 'create table: ' . $name . PHP_EOL;
                }
                self::createAsstesTable($name);
                $total++;
            }
        } else {
            if ($echo) {
                echo 'create table: ' . $tabname . PHP_EOL;
            }
            self::createAsstesTable($tabname);
            $total++;
        }

        return $total;
    }

    /**
     * 安装日志表
     *
     * @param  [type]  $tabname  [description]
     * @param  [type]  $tabtotal [description]
     * @param  boolean $echo     [description]
     * @return [type]            [description]
     */
    public static function installLog($tabname, $tabtotal, $echo = true)
    {
        $total = 0;
        if ($tabtotal > 1) {
            for ($i = 0; $i < $tabtotal; $i++) {
                $name = $tabname . '_' . $i;
                if ($echo) {
                    echo 'create table: ' . $name . PHP_EOL;
                }
                self::createLogTable($name);
                $total++;
            }
        } else {
            if ($echo) {
                echo 'create table: ' . $tabname . PHP_EOL;
            }
            self::createLogTable($tabname);
            $total++;
        }

        return $total;
    }

    /**
     * 创建资产表
     *
     * @param  [type] $name 表名
     * @return [type]       [description]
     */
    public static function createAsstesTable($name = 'asset_balance')
    {
        $assets_sql = <<<SQL
CREATE TABLE `%s` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `uid` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
    `name` varchar(20) NOT NULL COMMENT '币种名称',
    `available` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '可用数量',
    `freeze` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '冻结数量',
    `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态1:有效,2:无效',
    `update_time` int(10) UNSIGNED NOT NULL COMMENT '更新时间',
    `create_time` int(10) UNSIGNED NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `user`(`uid`),
    UNIQUE KEY `user_coin`(`uid`, `name`)
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '资产表';
SQL;

        $sql = sprintf($assets_sql, $name);
        return Db::execute($sql);
    }

    /**
     * 创建资产流水表
     *
     * @param  [type] $name 表名
     * @return [type]       [description]
     */
    public static function createLogTable($name = 'asset_balance_log')
    {
        $log_sql = <<<SQL
CREATE TABLE `%s`  (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `uid` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
    `from`int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '来源用户ID',
    `type`tinyint(1) UNSIGNED NOT NULL COMMENT '类型: 1充值;2扣减;3可用转冻结;4冻结转可用;5兑换支出;6兑换收入;7转出;8转入',
    `name` varchar(20) NOT NULL COMMENT '币种名称',
    `source` varchar(30) NOT NULL DEFAULT '' COMMENT '来源',
    `available_before` bigint(20) UNSIGNED NOT NULL COMMENT '操作前数量',
    `available_num` bigint(20) UNSIGNED NOT NULL COMMENT '操作数量',
    `available_after` bigint(20) UNSIGNED NOT NULL COMMENT '操作后数量',
    `freeze_before` bigint(20) UNSIGNED NOT NULL COMMENT '操作前冻结数量',
    `freeze_num` bigint(20) UNSIGNED NOT NULL COMMENT '操作冻结数量',
    `freeze_after` bigint(20) UNSIGNED NOT NULL COMMENT '操作后冻结数量',
    `create_time` int(10) UNSIGNED NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `user`(`uid`)
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '资产流水表';
SQL;

        $sql = sprintf($log_sql, $name);
        return Db::execute($sql);
    }
}
