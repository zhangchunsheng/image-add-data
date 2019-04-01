<?php

namespace LM;

class Filter {

    public static function getYoncheDomains() {
        return array(
            'luomor.com',
            'luomor.org',
            'luomor.cn',
            '4001111777.com',
            'luomor.name',
            'chexiao.me',
            'luomor-inc.com',
        );
    }

    /**
     *
     *
     * */
    public static function int($str, $min = null, $max = null) {
        $options = array(
            'options' => array('min_range' => $min, 'max_range' => $max),
            'flags' => FILTER_FLAG_ALLOW_OCTAL | FILTER_FLAG_ALLOW_HEX,
        );
        return filter_var($str, FILTER_VALIDATE_INT, $options);
    }

    public static function float($str) {
        $options = array(
            'flags' => FILTER_FLAG_ALLOW_THOUSAND
        );
        return filter_var($str, FILTER_VALIDATE_FLOAT, $options);
    }

    /**
     *
     *  
     * */
    public static function url($str, $allowDomains = null) {
        $options = array();
        $url = filter_var($str, FILTER_VALIDATE_URL, $options);
        if ($url && (strncmp($url, 'http:', 5) === 0 || strncmp($url, 'https:', 6) === 0)) {
            if ($allowDomains === null) {
                $allowDomains = self::getYoncheDomains();
            } else if (is_string($allowDomains)) {
                $allowDomains = array($allowDomains);
            } else if (!is_array($allowDomains)) {
                return false;
            }
            $host = parse_url($url, PHP_URL_HOST);
            if ($host) {
                $domain = substr($host, strrpos($host, ".", -4));
                return in_array($domain, $allowDomains) ? $url : false;
            }
            return $url;
        }
        return false;
    }

    public static function ip($str) {
        return filter_var($str, FILTER_VALIDATE_IP);
    }

    public static function email($str, $allowDomains = null) {
        return filter_var($str, FILTER_VALIDATE_EMAIL);
    }

    public static function boolean($str) {
        return filter_var($str, FILTER_VALIDATE_BOOLEAN);
    }

    public static function regexp($str, $regexp) {
        $options = array(
            'options' => array('regexp' => $regexp)
        );
        return filter_var($str, FILTER_VALIDATE_REGEXP, $options);
    }

    public static function str($str) {
        return $str;
    }

    // 国家编号: USA,Canada:1,梵蒂冈:3906698
    public static function mobile($str) {
        if (empty($str)) {
            return false;
        }
        if ($str[0] !== '+') {

            return self::regexp($str, "/^1[345678]\d{9}$/");
        } else {
            return self::regexp($str, "/^(\+\d{1,7}\-)?1[345678]\d{9}$/");
        }
    }

}
