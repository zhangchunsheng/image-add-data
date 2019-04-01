<?php

require __DIR__ . '/../init.inc.php';

define("CRON_CRM_OPERATOR_LOGIN_NAME", "cron");

function getCronOperatorId() {
    return 1;
}

$__last_handler__ = null;

function daemonErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
    global $__last_handler__;
    if (is_callable($__last_handler__)) {
        $__last_handler__($errno, date("[Y-m-d H:i:s]") . $errstr, $errfile, $errline, $errcontext);
    } else {
        error_log(date("[Y-m-d H:i:s]") . " no= $errno ; str= $errstr ; file= $errfile : $errline;");
    }
}

$__last_handler__ = set_error_handler("daemonErrorHandler", E_ALL | E_STRICT);

$__last_exception_handler__ = null;

/**
 * @param $exception Exception
 */
function daemonExceptionHandler($exception) {
    $result = '';
    error_log(date("[Y-m-d H:i:s]") . $exception->getMessage() . " $result " . $exception->getTraceAsString());
}

$__last_exception_handler__ = set_exception_handler("daemonExceptionHandler");

