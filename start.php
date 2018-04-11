<?php
/**
 * 获取环境变量的方法
 * @param $name
 * @param string $default
 * @return array|false|string
 */
function get_env($name, $default = '')
{
    return getenv(strtoupper($name)) === false ? $default : getenv(strtoupper($name));
}

define('PACKAGE_EOF',get_env('PACKAGE_EOF', '_pms_'));
define('TCP_SERVER_HOST', get_env('TCP_SERVER_HOST','pms_proxy'));
define('TCP_SERVER_PORT', get_env('TCP_SERVER_PORT',9502));
include 'client.php';
