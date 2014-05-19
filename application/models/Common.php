<?php

/**
 * @name CommonModel
 * @desc 通用类
 * @author vic
 */
class CommonModel {

    public function __construct() {
        $this->ssodb = Factory::getDBO('development_sso');
        $this->hydb = Factory::getDBO('development_canyin');
    }
    
    public function checkResend($mobile,$time)
    {
        $query = "select datetime from mobile_code where mobile='{$mobile}' AND datetime > '{$time}'";
        $this->ssodb->setQuery($query);
        $result = $this->ssodb->loadResult();
        return $result;
    }
    
    public function recordSendedSms($param) {
        $query = "insert mobile_code set mobile='$param[mobile]',capture='$param[capture]',type='$param[type]',content='$param[content]',status='$param[status]',used='$param[used]',datetime='$param[datetime]';";
        return $this->ssodb->query($query);
    }
    
    public function checkPhoneVerify($mobile, $capture)
    {
    	$query="select mobile,capture,datetime from mobile_code where mobile='$mobile' and capture='$capture' AND used = 1 ORDER BY datetime DESC LIMIT 1";
    	$this->ssodb->setQuery($query);
        $result = $this->ssodb->loadAssoc();
        return $result;
    }

}
