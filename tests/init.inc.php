<?php
/**
 * Created by PhpStorm.
 * User: peterzhang
 * Date: 25/12/16
 * Time: 10:04 PM
 */
define("ROOT", dirname(dirname(__FILE__)));

spl_autoload_register(function ($class) {
    $class = str_replace("\\", "/", $class);
    $file = '';
    // model && controller && service
    $pre = strtok($class, '/');
    if ($pre === 'controllers') {
        $file = ROOT . "/app/$class.php";
        // library
    } else if ($pre === 'LM') {
        $file = ROOT . "/lib/$class.php";
        // lang
    } else if ($pre === 'lang') {
        $file = ROOT . "/$class.php";
        // pear
    } else {
        $class = str_replace("_", "/", $class);
        $file = "/usr/share/pear/$class.php";
    }
    if ($file && is_file($file)) {
        require $file;
    }
});

$config = require ROOT . '/config/config.inc.php';

$config = new Yaf\Config\Simple($config, $readOnly = true);

Yaf\Registry::set("config", $config);

function g($arr, $key, $default = '') {
    return isset($arr[$key]) ? $arr[$key] : $default;
}