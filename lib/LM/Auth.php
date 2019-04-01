<?php

namespace LM;

use LM\Util;

class Auth {
    public static function allow($rightCode, $appId = 1) {
        return 1;
    }

    public static function getUserId($appId = 1) {
        return 1;
    }

    public static function getUserName($appId = 1) {
        return "test";
    }

    public static function getSSOUrl() {
        return "https://iad-admin.luomor.com/sso";
    }

    public static function getLogoutUrl() {
        return "https://iad-admin.luomor.com/sso/logout";
    }
}
