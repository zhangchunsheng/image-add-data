<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

class PostModel {
    public static function findById() {
        return array(
            "user_id" => 5569,
        );
    }

    public static function findAll() {
        return array(
            array(
                "user_id" => 5569,
            ),
        );
    }
}
