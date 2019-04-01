<?php


namespace LM\Db;

use LM\Db;

class ManagerEx {
    /**
    *
    *
    *
    *
    **/
    private static $_servers;
    private static $_readFarms;
    private static $_writeFarms;

    private static $_instanceList;
    /**
    *
    * @param array $servers
    *      array(
    *          "server1" => array("server" => "127.0.0.1", "port" => 3306, "username" => 'luomor",
    *                             "password" => "", "db_format" => "%s_%03d", "database_type" => "mysql"),
    "          "server2" => array("server" => "127.0.0.1", "port" => 3306, "username" => "luomor",
    *                             "password" => "", "db_format" => "%s_%03d", "database_type" => "mysql"),
    *      )
    * @param array $writeFarms
    *      array(
    *          "default" => "server1", // global
    *          "yc_core" => array( // yc_core_001 yc_core_002 ... yc_core_256
    *              "total" => 256,
    *              "algorithm" => "range",
    *              "servers" => array("1-128" => "server1", "129-256" => "server1")
    *          ),
    *          "yc_crm_common" => array( // yc_crm_common_001 yc_crm_common_002
    *              "total" => 256,
    *              "algorithm" => "modulus", // 模数
    *              "servers" +> array("server1","server1"),
    *          ),
    *      )
    * @param array $readFarms same as $writeFarms
    * 
    * @return void
    **/

    public static function setConfigEx(array $config) {
        $servers = isset($config['servers']) ? $config['servers'] : array();
        $writers = isset($config['writers']) ? $config['writers'] : array();
        $readers = isset($config['readers']) ? $config['readers'] : array();
        self::setConfig($servers, $writers, $readers);
    }

    public static function setConfig(array $servers, array $writeFarms, array $readFarms = array()) {
        self::$_servers = $servers;
        self::$_writeFarms = self::_parseFarms($writeFarms, $servers);
        self::$_readFarms = $readFarms ? self::_parseFarms($readFarms, $servers) : self::$_writeFarms;
    }

    private static function _parseFarms($farms, $allServers) {
        $ret = array();
        foreach($farms as $name => $config) {
            $data = array();
            if($config && is_string($config)) {
                // check if $config is a server nickname
                if(!isset($allServers[$config])) {
                    throw new Exception("Cann't find server '{$config}' in config");
                }
                $data = array("total" => 1, "servers" => array($config));
            } else if(is_array($config) && count($config) > 0) {
                // check if $config['servers'] are server nicknames
                $servers = isset($config['servers']) ? $config['servers'] : array();
                foreach($servers as $server) {
                    if(!isset($allServers[$server])) {
                        throw new Exception("Cann't find server '{$server}' in config");
                    } 
                }
                $servers = array();
                $data = array("total" => $config['total'], 'servers' => $config['servers']);
                if($config['algorithm'] == 'range') {
                    foreach($data['servers'] as $range => $server) {
                        list($from, $to) = explode("-", $range); 
                        for($i = $from; $i <= $to; $i++) {
                            $servers[$i] = $server;
                        }
                    } 
                } else if($config['algorithm'] == 'modulus') {
                    $tmpServers = $config['servers'];
                    $count = count($tmpServers);
                    for($i = 0; $i < $data['total']; $i ++) {
                        $t = $i % $count;
                        $servers[$i + 1] = $tmpServers[$t];
                    }
                } else {
                    throw new Exception("We Don't support '{$config['algorithm']}' algorithm for database sharding");
                }
                $data['servers'] = $servers;
            } else {
                throw new Exception("Config cann't be empty for database '$name'");
            }
            $ret[$name] = $data;
        }
        return $ret;
    }

    /**
    *
    *
    *
    **/
    public static function getInstance($forWrite = false, $dbname = 'default', $farm = null) {
        $farms = $forWrite ? self::$_writeFarms : self::$_readFarms;
        $name = isset($farms[$dbname]) ? $dbname : (isset($farms["default"]) ? "default" : "");
        if(empty($name)) {
            throw new Exception("Can't find dbname '{$dbname}' config");
        }
        $server = $farms[$name]['servers'][$farm === null ? 0 : $farm];
        $config = self::$_servers[$server];
        
        /*
        $format = isset($config['db_format']) ? $config['db_format'] : "%s_%03d";
        $config['database_name'] = $name = $farm === null ? $dbname : sprintf($format, $dbname, $farm);
        */

        $key = "{$forWrite}:{$config['database_type']}:{$config['server']}:{$config['port']}:{$name}:{$config['username']}";
        if(!isset(self::$_instanceList[$key])) {
            $config['writer'] = $forWrite;
            self::$_instanceList[$key] = new Db($config);
        }
        return self::$_instanceList[$key];
    }

    public static function getReader($dbname = 'default', $farm = null) {
        return self::getInstance($forWrite = false, $dbname, $farm);
    }

    public static function getWriter($dbname = 'default', $farm = null) {
        return self::getInstance($forWrite = true, $dbname, $farm);
    }
}
