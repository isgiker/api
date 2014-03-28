<?php

/**
 * @name Weixin_ApiModel
 * @desc 微信接口数据
 * @author vic
 */
class Weixin_ApiModel {

    public function __construct() {
        $this->ssodb = Factory::getDBO('development_sso');
    }
    
    //获取微信公众号信息
    public function getWxappInfo($wxId) {
        if(!$wxId) return false;
        $query = "select a.* from business_wx a where a.wxId='$wxId'";
        $this->ssodb->setQuery($query);
        $result = $this->ssodb->loadAssoc();
        return $result;
    }
    
    public function bind($wxid,$mobile,$wxUserInfo){
        if(!$wxid || !$mobile || !$wxUserInfo){
            return false;
        }
        $bindTime=time();
        $query = "insert business_wx_fans set wxId='$wxid', "
                . "fansMobile='$mobile',"
                . "subscribe='$wxUserInfo[subscribe]',"
                . "openid='$wxUserInfo[openid]', "
                . "nickname='$wxUserInfo[nickname]',"
                . "sex='$wxUserInfo[sex]',"
                . "city='$wxUserInfo[city]',"
                . "country='$wxUserInfo[country]',"
                . "province='$wxUserInfo[province]',"
                . "language='$wxUserInfo[language]',"
                . "headimgurl='$wxUserInfo[headimgurl]',"
                . "subscribeTime='$wxUserInfo[subscribe_time]',"
                . "bindTime='$bindTime'"
                ;
        return  $this->ssodb->query($query);
    }
    
    public function checkBind($wxid,$mobile) {
        $query = "select a.fansId from `business_wx_fans` as a where a.`wxId` ='$wxid' and a.fansMobile='$mobile'";
        $this->ssodb->setQuery($query);
        $result = $this->ssodb->loadAssoc();
        return $result;
    }

}
