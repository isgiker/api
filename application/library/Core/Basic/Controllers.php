<?php

class Core_Basic_Controllers extends Yaf_Controller_Abstract {

    public $_layout = false;
    protected $_layoutVars = array();

    /**
     * 加载Layout模板
     */
    public function render($action, array $tplVars = NULL) {
        if ($this->_layout == true) {
            $this->_layoutVars['actionContent'] = parent::render($action, $tplVars);
            return parent::render('../layout/layout', $this->_layoutVars);
        } else {
            return parent::render($action, $tplVars);
        }
    }

    /**
     * 监测是否异步请求
     */
    public function isAjax() {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && "XMLHttpRequest" === $_SERVER['HTTP_X_REQUESTED_WITH']) {

            return true;
        } else {

            return false;
        }
    }

    /**
     * 监测是否post
     */
    public function isPost() {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {

            return true;
        } else {

            return false;
        }
    }

    /**
     * 监测是否get
     */
    public function isGet() {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {

            return true;
        } else {

            return false;
        }
    }

    /**
     * 输出请求成功的json数据
     * data：返回的数据对象
     * url:请求成功后需要跳转的地址
     */
    public function ok($data = '', $url = '', $msg = null) {
        $strResult = json_encode(array(
            'result' => 'ok',
            'data' => $data,
            'msg' => $msg,
            'url' => $url
        ));
        $strCb = $this->getRequest()->getQuery('cb');
        if (!empty($strCb)) {
            $strResult = $strCb . '(' . $strResult . ');';
        }
        header('Content-type: application/x-javascript;charset=UTF-8');
        echo $strResult;
        exit;
    }
    /**
     * 输出请求失败的json数据
     * code：错误码
     * msg:错误信息
     */
    public function err($code = "", $msg = "") {
        $strResult = json_encode(array(
            'result' => 'err',
            'code' => $code,
            'msg' => $msg
        ));
        $strCb = $this->getRequest()->getQuery('cb');
        if (!empty($strCb)) {
            $strResult = $strCb . '(' . $strResult . ');';
        }
        header('Content-type: application/x-javascript;charset=UTF-8');
        echo $strResult;
        exit;
    }

    //生成随机验证码
    protected function getCaptureCode($length, $onlyNumber = 1) {
        $chars = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
        if (empty($onlyNumber)) {
            $chars = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "p", "q", "r", "s", "t", "u", "v", "x", "y", "z", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        }
        shuffle($chars);
        $ret = array();

        for ($i = 0; $i < $length; $i++) {
            $ret[] = $chars[$i];
        }

        return join('', $ret);
    }

    /**
     * 发送手机验证码
     */
    protected function sendSms($phone, $content) {
        Yaf_Loader::import('Snoopy.class.php');
        $url = "http://3tong.net/http/SendSms"; //地址
        $account = 'dh1469'; //帐号
        $pwd = md5('1469.HG'); //密码
        srand();
        $submit_vars['Account'] = $account;
        $submit_vars['Password'] = $pwd;
        $submit_vars['Phone'] = $phone;
        $submit_vars['Content'] = $content;
        $snoopy = new Snoopy;
        $snoopy->rawheaders['Pragma'] = 'no-cache';
        $snoopy->submit($url, $submit_vars);
        $Html = $snoopy->results;
        //echo($Html);
        //处理返回值
        if (preg_match("/<response>(.*)<\/response>/isU", $Html, $tDate)) {
            $val = $tDate[1];
            return $val;
            //     if ($val > 0) 
            //     return true;
            //     else {
            //         if ($val == - 2) {
            //             //帐号被禁用，通知管理员代码
            //         } elseif ($val == - 3) {
            //             //帐号余额不足，通知管理员代码
            //         } elseif ($val == - 10) {
            //             //账户当日发送短信量已经超过允许的每日最大发送量，通知管理员代码
            //         }
            //         return false;
            //     }
            // } else {
            //     return false;
            // }
        }
        
        return false;
    }
    
    public function jsAlert($msg) {
        $js="<script type='text/javascript'>alert('$msg');</script>";
        echo $js;exit;
    }

}
