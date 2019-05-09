# mon-assets

#### 介绍

PHP资产管理引擎, 支持composer、HTTP调用。

#### 使用场景

应用具有多种类型的资产，如： 游戏积分、币种资产或者计数器等业务场景时，可使用该引擎做资产管理。

#### 安装教程

##### HTTP使用

1. clone下载本项目， 并使用composer安装依赖

```
git clone xxxxx

composer install
```

2. 编辑配置config目录下的config.php文件。【配置说明请查看wiki】

3. 执行应用目录下的init, 创建数据库

```
php init
```

4. 启动http服务器，入口执行web.php

```
php -S 0.0.0.0:8888 web.php
```

##### commpoer使用

1. composer安装本项目

```
composer require xxxx
```

2. 编辑配置config目录下的config.php文件。【配置说明请查看wiki】

3. 执行应用目录下的init, 创建数据库

```
php init
```

#### API文档

* 请查看wiki