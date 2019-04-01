<?php

namespace LM;

class Crumb {
    CONST SALT = "ghijklmnocxzczqpqrstuvwxyz";

    static $ttl = 7200;

    static public function challenge($data) {
        return hash_hmac('md5', $data, self::SALT);
    }

    static public function issueCrumb($uid, $action = -1) {
        $i = ceil(time() / self::$ttl);
        return substr(self::challenge($i . $action . $uid), -12, 10);
    }

    static public function verifyCrumb($uid, $crumb, $action = -1) {
        $i = ceil(time() / self::$ttl);

        if(substr(self::challenge($i . $action . $uid), -12, 10) == $crumb ||
            substr(self::challenge(($i - 1) . $action . $uid), -12, 10) == $crumb)
            return true;

        return false;
    }
}
