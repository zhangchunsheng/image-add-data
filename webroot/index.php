<?php
define("APP_PATH",  realpath(dirname(__FILE__) . "/../"));
define("PUBLIC_PATH",  dirname(__FILE__));

ini_set("yaf.use_namespace", 1);
ini_set("yaf.library", "/usr/share/pear/");

require __DIR__ . '/../init.inc.php';
require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config/config.inc.php';

$config = new Yaf\Config\Simple($config, $readOnly = true);
Yaf\Registry::set("config", $config);

function g($arr, $key, $default = '') {
    return isset($arr[$key]) ? $arr[$key] : $default;
}

spl_autoload_register(function($clazz) {
    $filename = str_replace('_', '/', $clazz);
    $filename = str_replace('\\', '/', $filename);

    if (is_file("/usr/share/pear/$filename.php")) {
        require "/usr/share/pear/$filename.php";
    }
});

try {

    $app  = new \Yaf\Application(APP_PATH . "/config/application.ini");

    $app->bootstrap() //call bootstrap methods defined in Bootstrap.php
        ->run();
} catch(Exception $e) {
    error_log($e);
}

