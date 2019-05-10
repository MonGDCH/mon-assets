<?php

$class = '\\Mon\\console\\App';
if(class_exists($class) && strtoupper(php_sapi_name()) === 'CLI'){
    $obj = \Mon\console\App::instance();

    $obj->add('mon-assets-install', \mon\assets\command\Install::class);
    $obj->add('mon-assets-server', \mon\assets\command\Server::class);
}