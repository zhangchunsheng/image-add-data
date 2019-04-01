<?php

namespace LM;

class Util {

    private static $_redisInstances = array();

    public static function getCurrentUrl() {
        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $schema = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? "https" : "http";
        $port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '';
        $port = ($port == '80' && $schema == "http") ||
                    ($port == "443" && $schema == "https") ? "" : (":" . $port);
        return $schema . "://" . $host . $port . html_entity_decode($requestUri);
    }

    private static $_salt = "4ff350bde75104703989e2139e9ed1c4";

    public static function encryptSource($source) {
        $sign = substr(sprintf("%u", crc32($source . self::$_salt)), -2);
        return $source . $sign;
    }

    public static function toAscii($str) {
        $from = array(
                "１","２","３","４","５","６","７","８","９","０","　",
                "～","！","＠","＃","％","＆","＊","（","）","—","＋","－","＝",
                "【","】","‘","“","：","；","《","》","，","。","？","／","｜","＼",
                "ａ","ｂ","ｃ","ｄ","ｅ","ｆ","ｇ","ｈ","ｉ","ｊ","ｋ","ｌ","ｍ",
                "ｎ","ｏ","ｐ","ｑ","ｒ","ｓ","ｔ","ｕ","ｖ","ｗ","ｘ","ｙ","ｚ",
                "Ａ","Ｂ","Ｃ","Ｄ","Ｅ","Ｆ","Ｇ","Ｈ","Ｉ","Ｊ","Ｋ","Ｌ","Ｍ",
                "Ｎ","Ｏ","Ｐ","Ｑ","Ｒ","Ｓ","Ｔ","Ｕ","Ｖ","Ｗ","Ｘ","Ｙ","Ｚ"
                );
        $to = array(
                "1","2","3","4","5","6","7","8","9","0"," ",
                "~","!","@","#","%","&","*","(",")","-","+","-","=",
                "[","]","'","\"",":",";","<",">",",",".","?","/","|","\\",
                "a","b","c","d","e","f","g","h","i","j","k","l","m",
                "n","o","p","q","r","s","t","u","v","w","x","y","z",
                "A","B","C","D","E","F","G","H","I","J","K","L","M",
                "N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
                );
        return str_replace($from, $to, $str);
    }

    public static function getIp(){
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if(getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if(getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else
            $ip = $_SERVER['REMOTE_ADDR'];
        return $ip;
    }

    /**
     * 根据给定的IP获取城市信息
     *
     *
     */
    public static function getCityByIp($ip) {
        $ret = $ip ? @geoip_record_by_name($ip) : false;
        $city = $ret && isset($ret['city']) ? $ret['city'] : '';
        $short = $city ? yc_geo_get_city_short_name($city) : $city;
        if($short) {
            return yc_geo_get_city_info($short);
        } else if($city) {
            return array(
                'name' => $ret['city'], 
                'en' => $ret['city'],
                'short' => $ret['city'], 
                'country' => $ret['country_code']
            );
        }
        return array('name' => '未知', 'en' => 'Unknown', 'short' => '', 'country' => 'CN');
    }

    public static function utf8Wordwrap($string, $width = 75, $break = "\n", $cut = false) {
        if($cut) {
            // Match anything 1 to $width chars long followed by whitespace or EOS,
            // otherwise match anything $width chars long
            $search = '/(.{1,' . $width . '})(?:\s|$)|(.{' . $width . '})/uS';
            $replace = '$1$2' . $break;
        } else {
            // Anchor the beginning of the pattern with a lookahead
            // to avoid crazy backtracking when words are longer than $width
            $search = '/(?=\s)(.{1,' . $width . '})(?:\s|$)/uS';
            $replace = '$1' . $break;
        }
        return preg_replace($search, $replace, $string);
    }

    /**
     * 获取 Redis 的实例
     *
     **/
    public static function getRedisInstance($db) {
        if(!isset(self::$_redisInstances[$db])) {
            $host = \Yaf\Registry::get("config")->cache->redis->host->toArray();
            $opts = array(
                'host' => $host,
                'prefix' => (int) $db,
                'timeout' => 3,
                'persistent' => 1,
            );
            $redis = new \LML_Cache_Redis($opts);

            self::$_redisInstances[$db] = $redis;
        }
        return self::$_redisInstances[$db];
    }

    /**
     * 是否是高峰期
     *
     **/
    public static function isFastigium() {
        return false;
        $now = date("Gi");
        return ($now >= 700 && $now <= 930) || ($now >= 1600 && $now <= 2100);
    }

    public static function getUserAgent() {
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        return $userAgent;
    }
    
	public static function multiCurl($hostUrls, $params = array(), $method = 'GET', $waitUsec = 0) {
        if(!$hostUrls || !is_array($hostUrls)) return false;
        $mh = curl_multi_init(); // multi curl handler
        $i = 0;
        $running = 0;
        $handle = array();
		foreach($hostUrls as $url => $host) {
	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $headers = array(
            	"User-Agent: around-management/1.0",
                "Host: $host",
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            if (count($params) > 0) {
                if('GET' == $method) {
                    $url .= '?' . http_build_query($params);
                } else {
                    if (is_array($params)) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                    } else {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                    }
                    curl_setopt($ch, CURLOPT_POST, true);
                }
            }

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_multi_add_handle($mh, $ch); // 把 curl resource 放进 multi curl handler 里
	        $handle[$i++] = $ch;
	    }

	     /* 执行 */
	    do {
	        curl_multi_exec($mh, $running);
	        if ($waitUsec > 0) /* 每个 connect 要间隔多久 */
	            usleep($waitUsec); // 250000 = 0.25 sec
	    } while ($running > 0);
	     
		/* 读取资料 */
	    $data = array();
	    foreach($handle as $i => $chr) {
	        $content  = curl_multi_getcontent($chr);
	        $data[$i] = (curl_errno($chr) == 0) ? $content : false;
	    }

	    error_log("curl_multi return:".print_r($data, true));
		/* 移除 handle*/
	    foreach($handle as $ch) {
	        curl_multi_remove_handle($mh, $ch);
	    }
	    
	    curl_multi_close($mh);
    	return $data;
    }
}
