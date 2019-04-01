<?php

namespace LM;

class Input {
    const YONGCHE_URL_ONLY = 0x01;
    const YONGCHE_URL_HOST_IGNORE = 0x02;

    const YONGCHE_URL_DEFAULT = 0x03; //\LM\Input::YONGCHE_URL_ONLY | \LM\Input::YONGCHE_URL_HOST_IGNORE;

    //we should filter all the not safe chars from the url - by guoxiaod
    private static $URL_NOT_SAFE_CHARS = array('<', '>', '"', "'");

    private static $validDomainRegex = array(
        self::YONGCHE_URL_ONLY => "/(\.luomor|^luomor|\.luomor-inc)\.(com|cn|org|name)$/"
    );

    static public function url($name, $flag = \LM\Input::YONGCHE_URL_DEFAULT){
        return filter_has_var(INPUT_GET, $name) ? 
            \LM\Input::filterUrl(filter_input(INPUT_GET, $name)) :
            \LM\Input::filterUrl(filter_input(INPUT_POST, $name));
    }

    static public function filterUrl($url, $flag = \LM\Input::YONGCHE_URL_DEFAULT) {
        return self::isValidUrl($url, $flag) ? 
            str_replace(self::$URL_NOT_SAFE_CHARS, "", filter_var($url, FILTER_SANITIZE_URL)) : null;
    }

    static public function isValidUrl($url, $flag = \LM\Input::YONGCHE_URL_DEFAULT) {
        $urlParts = parse_url($url);
        if(isset($urlParts['host']) && preg_match(\LM\Input::$validDomainRegex[\LM\Input::YONGCHE_URL_ONLY], $urlParts['host'])) {
            return true;
        } else if(!isset($urlParts['host']) && ($flag & \LM\Input::YONGCHE_URL_HOST_IGNORE)){
            return true; 
        }
        return false;
    }

    static public function isValidMobile($mobile) {
        return preg_match(\Yaf\Registry::get("config")->cellphoneVerifyPattern, $mobile);
    }

    static public function get($name, $default = '') {
        if(!empty($_GET[$name])&&is_array($_GET[$name])){
            return $_GET[$name];
        }
        return isset($_GET[$name]) ? trim($_GET[$name]) : $default;
    }

    static public function post($name, $default = '') {
        if(!empty($_POST[$name])&&is_array($_POST[$name])){
            return $_POST[$name];
        }
        return isset($_POST[$name]) ? trim($_POST[$name]) : $default;
    }

    static public function request($name, $default = '') {
        if(!empty($_REQUEST[$name])&&is_array($_REQUEST[$name])){
            return $_REQUEST[$name];
        }
        return isset($_REQUEST[$name]) ? trim($_REQUEST[$name]) : $default; 
    }

    static public function int($name, $default = 0) {
        return (int) self::request($name, $default);
    }

    static public function email($name, $default = '') {
        return isset($_POST[$name]) ? filter_input(INPUT_POST, $name, FILTER_VALIDATE_EMAIL) : $default;
    }

}
