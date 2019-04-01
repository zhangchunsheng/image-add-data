<?php

namespace LM\Api;

use LM;
use LM\HttpRequest;

class AbstractApi {

	const ST_OK = 1;
    const ST_FROZEN = 2;
    const ST_DELETE = -1;
    
    protected $_api;
    protected $_key;

    public function __construct() {
        $class = get_called_class();
        // \\LM\\Api\\<key>\\<Controller>
        $pos = strpos($class, "\\", 7);
        $key = substr($class, 7, $pos > 0 ? $pos - 7 : strlen($class));
        $key = strtolower($key);
        $api = \Yaf\Registry::get("config")->url->$key;
        if (empty($api)) {
            throw new \Exception("config[url][$key] cann't be empty");
        }
        $this->_ApiClass = $key;
        $this->_api = $api[strlen($api) - 1] == '/' ? $api : ($api . '/');
    }

    public static function getInstance() {
        static $_instance = NULL;
        if (empty($_instance)) {
            $_instance = new static();
        }
        return $_instance;
    }

    protected function _getApi($method) {
        $class = get_called_class();
        $controller = substr($class, strrpos($class, "\\") + 1);
        return $this->_api . "$controller/$method";
    }

    protected function _post($method, $args) {
        $api = $this->_getApi($method);
        $timer = new \LM\Timer();
        $ret = HttpRequest::post($api, current($args));
        $ms = $timer->getMs();
        \LM\LoggerHelper::INFO('API_' . $this->_ApiClass, $ms, 'POST', ['api' => $api, 'args' => current($args), 'result' => $ret]);
        return $ret;
    }

    protected function _get($method, $args) {
        $api = $this->_getApi($method);
        $timer = new \LM\Timer();
        $ret = HttpRequest::get($api, current($args));
        $ms = $timer->getMs();
        \LM\LoggerHelper::INFO('API_' . $this->_ApiClass, $ms, 'GET', ['api' => $api, 'args' => current($args), 'result' => $ret]);
        return $ret;
    }

    public function _return($str) {
        $ret = json_decode($str, true);
        $code = g($ret, 'ret_code', -999);
        $msg = g($ret, 'ret_msg', 'no msg');
        $result = g($ret, 'result', $code == -999 ? $str : '');
        if ($code == 200) {
            return $result;
        }
        throw new \LM\Api\Exception($msg, $code);
    }

}
