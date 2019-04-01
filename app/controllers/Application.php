<?php

use LM\Input;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * The main application controller.
 *
 * All application controllers may inherit from this controller.
 * This controller uses Layout class (@see lib/Layout.php)
 */
class ApplicationController extends Yaf\Controller_Abstract {
    const RET_OK = 200;
    const RET_NOT_MODIFIED = 304; // 未修改
    const RET_INVALID_PARAM = 400; // 参数错误
    const RET_NOT_AUTH = 401;  // 未授权
    const RET_NOT_FOUND = 404;  // 未找到
    const RET_FORBIDDEN = 403; // 无权限
    const RET_ONLY_ONCE = 409; // 数据重复
    const RET_HTTP_ERROR = 410; // 访问外部接口错误
    const RET_NOT_BEGIN = 412; // 未开始
    const RET_NOT_MATCH = 417; // 不符合条件
    const RET_NOT_ENOUGH = 418; // 不足
    const RET_TOO_MANY = 429;  // 访问太频繁
    const RET_RETRY = 449;    // 稍候重试
    const RET_DB_ERROR = 450;  // 数据库错误
    const RET_EXPIRE = 498;
    const RET_UPGRADE = 426;
    const RET_CONFLICT = 480; //冲突
    const RET_UNKNOWN = 500;  // 未知错误
    const RET_NOT_SUPPORT = 415; // 不支持

    /**
     * The name of layout file.
     *
     * The name of layout file to be used for this controller ommiting extension.
     * Layout class will use extension from application config ini. 
     *
     * @var string
     */
    protected $layout;

    /**
     * The session instance.
     *
     * Yaf\Session instance to be used for this application.
     *
     */
    protected $session;

    /**
     * A Yaf\Config\Ini object that contains application configuration data.
     * 
     * @var Yaf\Config\Ini
     */
    private $config;

    protected $_column_names = array(

    );

    /**
     * Initialize layout and session.
     *
     * In this method can be initialized anything that could be usefull for 
     * the controller.
     *
     * @return void
     */
    public function init() {
        // Set the layout.
        $this->getView()->setLayout($this->layout);

        // Assign application config file to this controller
        $this->config = Yaf\Application::app()->getConfig();

        // Assign config file to views
        $this->getView()->config = $this->config;

        $this->_init();
    }

    /**
     * When assign a public property to controller, this property will be 
     * available to action view template too.
     *
     * @param string $name  the name of the property
     * @param mixed  $value the value of the property
     *
     * @return void 
     */
    public function __set($name, $value) {
        $this->$name = $value;
        $this->getView()->assignRef($name, $value);
    }

    public function getConfig() {
        return $this->config;
    }

    /**
     * Cancel current action proccess and forward to {@link notFound()} method.
     *
     * @return false
     */
    public function forwardTo404() {
        $this->forward('Index', 'application', 'notFound');
        $this->getView()->setScriptPath($this->getConfig()->application->directory 
            . "/views");
        header('HTTP/1.0 404 Not Found');
        return false;       
    }

    /**
     * Renders a 404 Not Found template view
     *
     * @return void
     */
    public function notFoundAction() {

    }

    protected $_operator;
    protected $_operatorId;
    protected $_config;
    protected $_crumbKey = "DATA";

    protected $_appId = 1;
    protected $_referrerUrl = "";
    protected static $_modelList = null;

    private function _init() {
        $this->_operatorId = \LM\Auth::getUserId();
        $this->_operator = \LM\Auth::getUserName();

        $config = \Yaf\Registry::get("config")->toArray();
        $this->_config = $config;

        $this->_referrerUrl = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "";

        $this->getView()->assignRef("referrerUrl", $this->_referrerUrl);
        $this->getView()->assignRef("appId", $this->_appId);
        $this->view = $this->getView();
        $this->view->assign('viewPath', APP_PATH . 'views');
        $this->view->assign('operatorId', $this->_operatorId);
        $this->view->assign('operator', $this->_operator);

        $this->view->assign('ssoUrl', $this->_getSSOUrl());

        $this->_issueCrumb();
    }

    protected function _issueCrumb() {
        $this->view->crumb = LM\Crumb::issueCrumb($this->_operator, $this->_crumbKey);
    }

    protected function _verifyCrumb() {
        $request = $this->getRequest();

        $crumb = '';
        if ($request->isPost()) {
            $crumb = LM\Input::post("crumb");
        } else {
            $crumb = LM\Input::get("crumb");
        }

        if($crumb) {
            return LM\Crumb::verifyCrumb($this->_operator, $crumb, $this->_crumbKey);
        }

        return false;
    }

    protected function _allow($rightCode) {
        return \LM\Auth::allow($rightCode);
    }

    protected function _getSSOUrl() {
        $url = \LM\Auth::getSSOUrl();

        $pattern = "/^(https:\/\/.+)\//";

        preg_match($pattern, $url, $matches);

        if(count($matches) > 1) {
            return $matches[1];
        } else {
            return $matches[0];
        }
    }

    protected function _getLogoutUrl() {
        return \LM\Auth::getLogoutUrl();
    }

    protected function _getFlagValue($fieldName) {
        $data = isset($_POST[$fieldName]) ? $_POST[$fieldName] : array();
        if(!is_array($data)) {
            $data = array($data);
        }

        $ret = 0;
        foreach($data as $r) {
            $ret |= $r;
        }
        return $ret;
    }

    protected function _getJoinValue($field, $splitter = ',') {
        $data = isset($_POST[$field]) ? $_POST[$field] : array();
        if(!is_array($data)) {
            $data = array($data);
        }
        return join($splitter, $data);
    }

    protected function _getClientIp() {
        $ip = self::getIp();
        $localip = isset($_COOKIE['I']) ? $_COOKIE['I'] : '';
        $localip = join(".", array_map("hexdec", str_split($localip, 2)));
        return $localip ? (($localip . ',') .  $ip) : $ip;
    }

    /**
     * @return null
     */
    static function getIp() {
        foreach(array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR') as $v) {
            if(isset($_SERVER[$v]) and !empty($_SERVER[$v]))
                return $_SERVER[$v];
        }
        return "";
    }

    protected function renderJsonEx($result, $code = 200, $msg = 'ok') {
        $this->renderJson(array("code" => $code, "msg" => $msg, "result" => $result));
    }

    /**
     * @param $class
     * @param $method
     * @param \Exception $e
     */
    protected function processException($class, $method, $e) {
        \LM\LoggerHelper::ERR('ACCESS_' . strtolower($class) . "_" . strtolower($method), $e->__toString());

        $code = $e->getCode();
        if($code <= 0) {
            $code = self::RET_INVALID_PARAM;
        }
        $msg = $e->getMessage();
        if($code != 200) {
            foreach($this->_column_names as $key => $value) {
                $msg = str_replace($key, $value, $msg);
            }
        }
        $this->renderJsonEx("", $code, $msg);
    }

    protected function renderJson(array $data) {
        $REQUEST     = $_REQUEST;
        $REQUEST_URI = g( $_SERVER, 'REQUEST_URI' );
        $protocal    = 'CURL';
        $accessLog = rtrim( strtolower( preg_replace( '/\/+/', '_', strtok( $REQUEST_URI, '?' ) ) ), '_' );
        isset( $REQUEST['password'] ) && $REQUEST['password'] = strlen( $REQUEST['password'] );
        $log = [
            $protocal,
            \LM\Util::getUserAgent(),
            'REQUEST_URI' => $REQUEST_URI,
            'REQUEST'     => $REQUEST,
            'RETURN'      => $data,
        ];
        if(DEBUG > 0 && isset($_SERVER['SERVER_NAME'])) {
            array_unshift($log, $_SERVER['SERVER_NAME']);
        }
        if($data["code"] == 200) {
            \LM\LoggerHelper::INFO("ACCESS" . $accessLog, $log);
        } else {
            \LM\LoggerHelper::ERR("ACCESS" . $accessLog, $log);
        }

        header("Content-Type: application/json; charset=utf8");
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function _response($header = array(), $body = array()) {
        \Yaf\Dispatcher::getInstance()->disableView();
        $response = new \Yaf\Response\Http();

        if (is_array($header) && count($header) > 0) {
            foreach ($header as $key => $val) {
                if (is_string($key) && !empty($key) && is_string($val) && !empty($val)) {
                    $response->setHeader($key, $val);
                }
            }
        }

        if (is_array($body)) {
            $response->setBody(json_encode($body));
        } else if (is_string($body)) {
            $response->setBody($body);
        } else {
            $response->setBody("");
        }

        $response->response();
    }

    protected function _processOperatorNames(&$result) {
        if (!is_array($result)) {
            return $result;
        }

        $operatorIds = array();

        foreach ($result as $key => $val) {
            if (array_key_exists('operator_id', $val)) {
                array_push($operatorIds, $val['operator_id']);
            }
        	if (array_key_exists('last_operator_id', $val)) {
                array_push($operatorIds, $val['last_operator_id']);
            }
        }

        if (empty($operatorIds)) {
            return $result;
        }

        $ssoApi = new \LM\Sso\SsoApi();
        $operatorNames = $ssoApi->getOperatorNameByIds($operatorIds);

        foreach ($result as $key => &$val) {
            if (array_key_exists('operator_id', $val) && array_key_exists($val['operator_id'], $operatorNames)) {
                $val['operator_name'] = $operatorNames[$val['operator_id']]['name'];
            } else if (array_key_exists('last_operator_id', $val) && array_key_exists($val['last_operator_id'], $operatorNames)) {
                $val['operator_name'] = $operatorNames[$val['last_operator_id']]['name'];
            } else {
                $val['operator_name'] = '';
            }
        }
        return $result;
    }

    protected function _processCoordinate(&$params) {
        $latLng = array(
            "lat" => array(
                "name" => "纬度",
                "min_value" => -90,
                "max_value" => 90,
            ),
            "lng" => array(
                "name" => "经度",
                "min_value" => -180,
                "max_value" => 180,
            )
        );
        $inCoordType = Input::post('in_coord_type', LML_Map::COORD_TYPE_MARS);
        if(!in_array($inCoordType, array_keys(LML_Map::$DICT_COORD_TYPES))) {
            throw new \Exception("in_coord_type参数错误");
        }
        foreach($latLng as $key => $value) {
            if(isset($params[$key])) {
                $data = $params[$key];
            } else {
                $data = Input::post($key, '');
            }
            if ($data != '') {
                if(abs($data) < 1000) {
                    $data *= 1000000;
                }
                $data = intval($data);
                if($data > ($value["max_value"] * 1000000) || $data < ($value["min_value"] * 1000000)) {
                    throw new \Exception($value["name"] . "范围不合法");
                }

                $params[$key] = $data;
            }
        }
        if(isset($params["lat"])) {
            $map = new LML_Map();

            $coordinate = array(
                'lng' => floatval(round($params['lng'] / 1000000, 6)),
                'lat' => floatval(round($params['lat'] / 1000000, 6))
            );

            $lngLat = $map->convertCoord($coordinate, $inCoordType, LML_Map::COORD_TYPE_MARS);

            $params['lng'] = round($lngLat['lng'] * 1000000);
            $params['lat'] = round($lngLat['lat'] * 1000000);
        }
    }

    protected function _processResultCoordinate(&$rows) {
        $outCoordType = Input::request('out_coord_type', LML_Map::COORD_TYPE_MARS);
        $outCoordType = explode(",", $outCoordType);

        $map = new LML_Map();

        foreach($rows as $key => &$value) {
            $value["out_coordinates"] = array();
            foreach($outCoordType as $oct) {
                $coordinate = array(
                    'lng' => floatval(round($value['lng'] / 1000000, 6)),
                    'lat' => floatval(round($value['lat'] / 1000000, 6))
                );

                $latLng = $map->convertCoord($coordinate, LML_Map::COORD_TYPE_MARS, $oct);

                $value["out_coordinates"][$oct] = $latLng;
            }
        }
    }
}
