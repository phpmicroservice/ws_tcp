<?php

define("SERVICE_NAME", "USER");# 设置服务名字
define('ROOT_DIR', __DIR__);
require ROOT_DIR . '/vendor/autoload.php';


define('TCP_SERVER_HOST', \pms\get_env('TCP_SERVER_HOST','pms_proxy'));
define('TCP_SERVER_PORT', \pms\get_env('TCP_SERVER_PORT',9502));
include 'client.php';
