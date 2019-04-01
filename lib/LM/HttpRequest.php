<?php

namespace LM;

class HttpRequest {
    const USER_AGENT = 'luomor-iad/1.0';

    public static function get($url, $parameters, array $options = array()) {
        return self::request("GET", $url, $parameters, $options);
    }

    public static function post($url, $parameters, array $options = array()) {
        return self::request("POST", $url, $parameters, $options);
    }

    /**
    *
    * @param string $method
    * @param string $url
    * @param mixed  $parameters
    * @param array  $options
    *
    * @return string|boolean
    **/
    public static function request($method, $url, $parameters, array $options = array()) {
        $headers = isset($options['headers']) ? $options['headers'] : array();
        $asproxy = isset($options['asproxy']) ? $options['asproxy'] : '';
        $request = new \http\Client\Request($method, $url, $headers);
        $options = array_merge(array( 
            'connecttimeout' => 10,
            'timeout' => 10,
            'redirect' => 3,
            'retrycount' => 3,
            'retrydelay' => 0.1,
            'useragent' => self::USER_AGENT,
        ), $options);
        $request->setOptions($options);
        if($method == 'GET') {
            $request->addQuery($parameters);
        } else {
            $body = new \http\Message\Body();
            //$request->setBody($body->addForm($parameters));
            $body->append(http_build_query($parameters));
            $request->setBody($body);
            $request->setContentType("application/x-www-form-urlencoded");
        }

        try {
            $client = new \http\Client;
            $client->enqueue($request)->send();

            $response = $client->getResponse($request);

            // just make the request as proxy
            if($asproxy) {
                $version = $response->getHttpVersion();
                $code = $response->getResponseCode();
                $status = $response->getResponseStatus();
                header(sprintf("HTTP/%s %s %s", $version, $code, $status));
                echo $response->getBody()->toString();
                exit;
            }

            if($response && $response->getResponseCode() == 200) {
                return $response->getBody()->toString();
            }
            $result = array(
                'code' => $response->getResponseCode(),
                'status' => $response->getResponseStatus(),
                'body' => $response->getBody()->toString()
            );
            throw new \Exception("$method $url failed");
        } catch (\http\Exception $e) {
            $result = array(
                'code' => $e->getCode(),
                'status' => $e->getMessage(),
                'body' => '',
            );
            throw new \Exception("$method $url failed :" . $e->getMessage(), $e->getCode());
        }
    }
}
