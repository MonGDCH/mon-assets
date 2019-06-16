# mon-assets

#### 介绍

PHP资产管理引擎, 支持composer、HTTP调用。

#### 使用场景

应用具有多种类型的资产，如： 游戏积分、币种资产或者计数器等业务场景时，可使用该引擎做资产管理。

#### 安装教程

##### HTTP使用

1. clone下载本项目， 并使用composer安装依赖

```bash
git clone https://github.com/MonGDCH/mon-assets.git

composer install
```

2. 编辑配置config目录下的config.php文件。

3. 执行应用目录下的init, 创建数据库

```bash
php init
```

4. 启动http服务器，入口执行web.php

```bash
php -S 0.0.0.0:8888 web.php
```

##### commpoer使用

1. composer安装本项目

```bash
composer require mongdch/mon-assets
```

2. 编辑配置config目录下的config.php文件。

3. 执行应用目录下的init, 创建数据库

```bash
php init
```

#### API文档

[请查看Wiki](https://github.com/MonGDCH/mon-assets/wiki) 

#### 版本

##### 1.0.3

* 优化代码，修复已知问题

##### 1.0.2

* 更新依赖为发布包

##### 1.0.1

* 优化代码结构
* 接入mon-console命令行Command指令