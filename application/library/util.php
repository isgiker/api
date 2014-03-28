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

    static public function curl_Http_Request($url, $data = null, $method = 'GET') {
        $ch = curl_init();
        $header = array("Accept-Charset: utf-8");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
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

}
