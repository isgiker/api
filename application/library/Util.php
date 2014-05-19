<?php

/**
 * @abstract通用类
 * 
 */
class Util {
    /*
     * 创建唯一标识符
     */

    static public function getUuid() {
        $uuid = uniqid(getmypid()) . mt_rand(1, 10000000000);
        return $uuid;
    }

    /**
     * @abstract获取订单编号
     */
    static public function getOrdersn() {
        $millisecond = sprintf("%.4f", microtime(true)) * 10000;
        return $millisecond . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * 获取用户IP
     */
    static public function getIp() {
        $onlineip = '';
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $onlineip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }
        return $onlineip;
    }

    /**
     * PHP HTTP 请求
     * @param type $url
     * @param int $port 80|443
     * @param type $data
     * @param type $method GET|POST
     * @return array|json|string
     */
    static public function sock_Http_Request($url, $port = 80, $data = '', $method = 'GET') {
        if (!trim($url)) {
            return false;
        }
        // parsing the given URL 
        $url_info = parse_url($url);

        switch ($url_info['scheme']) {
            case 'https':
                $scheme = 'ssl://';
                $port = 443;
                break;
            case 'http':
            default:
                $scheme = '';
        }


        $url_info["path"] = $url_info["path"] . '?' . $url_info['query'];

        $end = "\r\n";
        // building GET-request: 
        $request.="$method " . $url_info["path"] . " HTTP/1.1$end";
        //如果不加下面这一句,会返回一个http400错误
        $request.="Host: " . $url_info["host"] . $end;
        //如果不加下面这一句,请求会阻塞很久
        $request.="Connection: close$end";
        $request.=$end;

        // building POST-request: 
        if ($method == 'POST') {
            // making string from $data 
            if (is_array($data) && $data) {
                $data_string = http_build_query($data);
            } elseif ($data) {
                $data_string = $data;
            }
            //POST数据
            //判断是否为json格式
            if (is_null(json_decode($data))) {
                $request.="Content-type: application/x-www-form-urlencoded$end";
            } else {
                $request.="Content-type: application/json; encoding=utf-8$end";
            }

            //POST数据的长度
            $request.="Content-length: " . strlen($data_string) . $end;
            //传递POST数据
            $request.=$data_string . $end;
            $request.=$end . $end;
        }
//print_r($request);exit;
        $fp = fsockopen($scheme . $url_info["host"], $port, $errno, $errstr, 30);
        fwrite($fp, $request);

        while (!feof($fp)) {
            $result .= fgets($fp, 128);
        }
        fclose($fp);

        return $result;
    }

    static public function curl_Http_Request($url, $data = null, $method = 'GET',$header=array(),$timeout=45) {
        $ch = curl_init();
        if(empty($header)){
            $header = array("Accept-Charset: utf-8");
            //send xml data
            //$header=array('Content-Type: text/xml; charset=utf-8');
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        //请求信息
//    $info = curl_getinfo($ch);

        if (curl_errno($ch)) {
            return false;
        } else {
            return $tmpInfo;
        }
    }
    
    function ihttp_request($url, $post = '', $extra = array(), $timeout = 60) {
        $urlset = parse_url($url);
        if (empty($urlset['path'])) {
            $urlset['path'] = '/';
        }
        if (!empty($urlset['query'])) {
            $urlset['query'] = "?{$urlset['query']}";
        }
        if (empty($urlset['port'])) {
            $urlset['port'] = '80';
        }

        if (function_exists('curl_init') && function_exists('curl_exec')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlset['scheme'] . '://' . $urlset['host'] . ($urlset['port'] == '80' ? '' : ':' . $urlset['port']) . $urlset['path'] . $urlset['query']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            if ($post) {
                curl_setopt($ch, CURLOPT_POST, 1);
                if (is_array($post)) {
                    $post = http_build_query($post);
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1');
            if (!empty($extra) && is_array($extra)) {
                foreach ($extra as $opt => $value) {
                    if (Util::strexists($opt, 'CURLOPT_')) {
                        curl_setopt($ch, constant($opt), $value);
                    }
                    if (is_numeric($opt)) {
                        curl_setopt($ch, $opt, $value);
                    }
                }
            }
            $data = curl_exec($ch);
            $status = curl_getinfo($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            if ($errno || empty($data)) {
                return false;
            } else {
                return $data;
            }
        }
        $method = empty($post) ? 'GET' : 'POST';
        $fdata = "{$method} {$urlset['path']}{$urlset['query']} HTTP/1.1\r\n";
        $fdata .= "Host: {$urlset['host']}\r\n";
        if (function_exists('gzdecode')) {
            $fdata .= "Accept-Encoding: gzip, deflate\r\n";
        }
        $fdata .= "Connection: close\r\n";
        if (!empty($extra) && is_array($extra)) {
            foreach ($extra as $opt => $value) {
                if (!Util::strexists($opt, 'CURLOPT_')) {
                    $fdata .= "{$opt}: {$value}\r\n";
                }
            }
        }
        $body = '';
        if ($post) {
            if (is_array($post)) {
                $body = http_build_query($post);
            } else {
                $body = urlencode($post);
            }
            $fdata .= 'Content-Length: ' . strlen($body) . "\r\n\r\n{$body}";
        } else {
            $fdata .= "\r\n";
        }
        $fp = fsockopen($urlset['host'], $urlset['port']);
        stream_set_blocking($fp, true);
        stream_set_timeout($fp, $timeout);
        if (!$fp) {
            return false;
        } else {
            fwrite($fp, $fdata);
            $content = '';
            while (!feof($fp))
                $content .= fgets($fp, 512);
            fclose($fp);
            return $content;
        }
    }
    
    function ihttp_get($url) {
        return ihttp_request($url);
    }

    function ihttp_post($url, $data) {
        $headers = array('Content-Type' => 'application/x-www-form-urlencoded');
        return ihttp_request($url, $data, $headers);
    }

    /**
     * 是否包含子串
     */
    static public function strexists($string, $find) {
        return !(strpos($string, $find) === FALSE);
    }
    
    
    function is_multiArrayEmpty($multiarray) {
        if (is_array($multiarray) and !empty($multiarray)) {
            $tmp = array_shift($multiarray);
            if (!is_multiArrayEmpty($multiarray) or !is_multiArrayEmpty($tmp)) {
                return false;
            }
            return true;
        }
        if (empty($multiarray)) {
            return true;
        }
        return false;
    }

}
