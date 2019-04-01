<?php

namespace LM;

use LML_Queue_Amqp;

class Queue {

    const DEFAULT_FARM = "luomor_f2";
    const DEFAULT_VHOST = "/basedata";

    protected static $_instanceList;
    
    public static function getDefaultInstance($exchange, $queue, $routingKey) {
        return self::getInstance(self::DEFAULT_FARM, self::DEFAULT_VHOST, $exchange, $queue, $routingKey); 
    }
    
    /** 
    *  获取队列的实例
    *
    **/
    public static function getInstance($farm, $vhost, $exchange, $queue, $routingKey) {
        // for test env
        $env = defined("AM_TEST_ENV") ? constant("AM_TEST_ENV") : '';
        if($env) {
            $exchange = $env . $exchange;
            $queue = $env . $queue;
        }

        $key = "$farm-$vhost-$exchange-$queue-$routingKey";

        if(self::$_instanceList && isset(self::$_instanceList[$key])) {
            return self::$_instanceList[$key];
        }

        $connection = config3("queue", "rabbitmq", $farm);
        $connection['vhost'] = $vhost;
        $config = array(
            'connection' => $connection,
            'exchange' => array(
                'name' => $exchange,
                'type' => AMQP_EX_TYPE_DIRECT,
                'flag' => AMQP_DURABLE,
            ),
            'queue' => array(
                array(
                    'name' => $queue,
                    'exchange_name' => $exchange,
                    'routing_key' => $routingKey,
                )
            )
        );

        $instance = new LML_Queue_Amqp($config);
        self::$_instanceList[$key] = $instance;
        return $instance;
    }

    public static function sendUpdateCarModelPhotoQueue($key, $value) {
        $instance = self::getInstance('luomor_f2', '/basedata', 'update_car_model_photo', 'update_car_model_photo_q', $key);
        return $instance->push($key, $value);
    }
}



