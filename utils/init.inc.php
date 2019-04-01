<?php
define("PS_ROOT", dirname(dirname(__FILE__)));
define("PROJECT", "AM");

$config = require PS_ROOT . '/config/config.inc.php';

$yafConfig = new Yaf\Config\Simple($config, $readOnly = true);
Yaf\Registry::set("config", $yafConfig);

function g($arr, $key, $default = '') {
    return isset($arr[$key]) ? $arr[$key] : $default;
}

function config($key) {
    global $config;
    return $config[$key];
}

function config2($key1, $key2) {
    global $config;
    return $config[$key1][$key2];
}

function config3($key1, $key2, $key3) {
    global $config;
    return $config[$key1][$key2][$key3];
}

spl_autoload_register(function ($class) {
    $class = str_replace("\\", "/", $class);
    $file = '';
    // model && controller && service
    $pre = strtok($class, '/');
    $suffix = substr($class, strlen($class) - 5);
    if ($suffix === 'Model') {
        $class = substr($class, 0, strlen($class) - 5);
        $file = PS_ROOT . "/app/models/$class.php";
        // library
    } else if ($pre === 'LM' ||
        $pre === 'OAuth2' ||
        $pre === 'League') {
        $file = PS_ROOT . "/lib/$class.php";
        // lang
    } else if ($pre === 'lang') {
        $file = PS_ROOT . "/$class.php";
        // pear
    } else {
        $class = str_replace("_", "/", $class);
        $file = "/usr/share/pear/$class.php";
    }
    if ($file && is_file($file)) {
        require $file;
    }
});

\LM\Db\Manager::setConfigEx(config("databases"));