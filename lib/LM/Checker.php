<?php

namespace LM;

use lang\zhCN\C;
use \LM\Filter;

class Checker {

    public static function InvalidArgumentException($field, $value, $error) {
        if ($field == C::INVALID_PARAM) {
            throw new \InvalidArgumentException(C::INVALID_PARAM);
        }
        if (is_array($value)) {
            $value = 'ARRAY';
        }
        throw new \InvalidArgumentException("$field: {$field}[$value] $error", 400);
    }

    public static function notNULL($str, $text = null) {
        $ret = !is_null($str);
        if ($ret || $text === null) {
            return $ret;
        }
        self::InvalidArgumentException($text, $str, "is NULL");
    }

    public static function number($value, $min = null, $max = null, $field = null) {
        $ret = is_numeric($value);
        if ($min !== null) {
            $ret = $ret && $value >= $min;
        }
        if ($max !== null) {
            $ret = $ret && $value <= $max;
        }
        if ($ret || $field === null) {
            return $ret;
        }
        self::InvalidArgumentException($field, $value, "is not between $min~$max");
    }

    public static function inArray($needle, $haystack, $field = NULL) {
        $ret = !in_array($needle, $haystack) ? false : $needle;
        if ($ret!==FALSE || $field === null) {
            return $ret;
        }
        self::InvalidArgumentException($field, $needle, ", it's not in [" . join(',', $haystack) . ']');
    }

    public static function numbers($values, $min = null, $max = null, $field = null) {
        foreach ($values as $value) {
            self::number($value, $min, $max, $field);
        }
    }

    public static function mobile($value, $fields = null) {
        $ret = Filter::mobile($value);
        if ($ret || $fields === null) {
            return $ret;
        }
        self::InvalidArgumentException($fields, $value, "is not mobile");
    }

    public static function url($value, $field = null) {
        $ret = Filter::url($value);
        if ($ret || $field === null) {
            return $ret;
        }
        self::InvalidArgumentException($field, $value, "is not url");
    }

    public static function email($value, $field = null) {
        $ret = Filter::email($value);
        if ($ret || $field === null) {
            return $ret;
        }
        self::InvalidArgumentException($field, $value, "is not email");
    }

    public static function error($field, $error = 'is error', $value = '') {
        self::InvalidArgumentException($field, $value, $error);
    }

    public static function notempty($value, $field = null) {
        $ret = !empty($value);
        if ($ret || $field === null) {
            return $ret;
        }
        self::InvalidArgumentException($field, $value, "is empty");
    }

    public static function notEmptyArray($arr, $field = null) {
        $ret = is_array($arr) && count($arr) != 0;
        if ($ret || $field === null) {
            return $ret;
        }
        self::InvalidArgumentException($field, $arr, "is empty array");
    }

    public static function mustFields($data, $mustFields) {
        foreach ($mustFields as $field) {
            self::notempty(isset($data[$field]) ? $data[$field] : NULL, $field);
        }
    }

    public static function readOnlyFields($data, $readOnlyFields) {
        foreach ($readOnlyFields as $field) {
            if (isset($data[$field])) {
                self::InvalidArgumentException($field, $data[$field], 'is readOnly');
            }
        }
    }

    public static function numberFields($data, $numberFields, $min = NULL, $max = NULL) {
        foreach ($numberFields as $field) {
            if (isset($data[$field])) {
                self::number($data[$field], $min, $max, $field);
            }
        }
    }

    public static function length($str, $min, $max, $text = null) {
        $ret = true;
        $len = mb_strlen($str, 'UTF-8');
        if ($min !== null) {
            $ret = $ret && $len >= $min;
        }
        if ($max !== null) {
            $ret = $ret && $len <= $max;
        }
        if ($ret || $text === null) {
            return $ret;
        }
        self::InvalidArgumentException($text, $str, " is not between [$min]~[$max] ");
    }
}
