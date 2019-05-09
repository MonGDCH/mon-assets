CREATE TABLE `asset_balance` (
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


CREATE TABLE `asset_balance_log`  (
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
