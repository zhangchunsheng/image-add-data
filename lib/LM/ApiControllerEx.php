<?php

namespace LM;

use Yaf\Controller_Abstract;

class ApiControllerEx extends Controller_Abstract {

    protected function _renderJsonError($code, $msg = '') {
        $this->_renderJson(array(
                "code" => $code,
                "msg" => $msg,
            )
        );
    }

    protected function _renderJsonEx($result, $code, $msg = '') {
        $this->_renderJson(array(
                "code" => $code,
                "msg" => $msg,
                "result" => $result
            )
        );
    }

    protected function _renderJson(array $data) {
        header("Content-Type: application/json; charset=utf8");
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
