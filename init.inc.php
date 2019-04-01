<?php
define("PS_ROOT", __DIR__);
define("PROJECT", "IAD");

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

class GlobalConfig {

    private static $_config;

    public static function init() {
        self::$_config = require PS_ROOT . '/config/config.inc.php';
    }

    public static function get($key) {
        return isset(self::$_config[$key]) ? self::$_config[$key] : null;
    }

    public static function get2($key1, $key2) {
        $config = self::$_config;
        return isset($config[$key1]) && isset($config[$key1][$key2]) ? $config[$key1][$key2] : null;
    }

    public static function get3($key1, $key2, $key3) {
        $config = self::$_config;
        $v1 = isset($config[$key1]) ? $config[$key1] : null;
        $v2 = isset($v1[$key2]) ? $v1[$key2] : null;
        return isset($v2[$key3]) ? $v2[$key3] : null;
    }

}

function config($key) {
    return GlobalConfig::get($key);
}

function config2($key1, $key2) {
    return GlobalConfig::get2($key1, $key2);
}

function config3($key1, $key2, $key3) {
    return GlobalConfig::get3($key1, $key2, $key3);
}

function gInt($arr, $key, $default = '') {
    return intval( isset($arr[$key]) ? $arr[$key] : $default);
}
GlobalConfig::init();

\LM\Db\Manager::setConfigEx(config("databases"));