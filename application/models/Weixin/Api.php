<?php

/**
 * @name Weixin_ApiModel
 * @desc 微信接口数据
 * @author vic
 */
class Weixin_ApiModel {

    public function __construct() {
        $this->ssodb = Factory::getDBO('development_sso');
        $this->hydb = Factory::getDBO('development_canyin');
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
                . "bindTime='$bindTime',"
                . "isUsed='0';"
                ;
        return  $this->ssodb->query($query);
    }
    
    public function checkBind($wxid,$mobile) {
        $query = "select a.fansId from `business_wx_fans` as a where a.`wxId` ='$wxid' and a.fansMobile='$mobile'";
        $this->ssodb->setQuery($query);
        $result = $this->ssodb->loadAssoc();
        return $result;
    }
    
    public function getShopId($wxid){
        if(!$wxid)return false;
        $query = "select a.shopId from `business_wx` as a where a.`wxId` ='$wxid' ";
        $this->ssodb->setQuery($query);
        $result = $this->ssodb->loadResult();
        return $result;
    }
    
    /**
     * 根据手机号获取用户id
     * @param type $mobile
     * @return string
     */
    public function getUserId($mobile){
        if(!$mobile)return false;
        $query = "select a.userId from `user` as a where a.`mobile` ='$mobile' ";
        $this->ssodb->setQuery($query);
        $result = $this->ssodb->loadResult();
        return $result;
    }


    public function scoreIsOpen($shopId){
        if(!$shopId)return false;
        $query = "select a.isScore from `canyin_shop_basic_ext` as a where a.`shopId` ='$shopId' ";
        $this->hydb->setQuery($query);
        $result = $this->hydb->loadResult();
        return $result;
    }
    
    public function getSubscribeScore($shopId){
        if(!$shopId)return false;
        $query = "select a.subscribeScore from `canyin_shop_score` as a where a.`shopId` ='$shopId' ";
        $this->hydb->setQuery($query);
        $result = $this->hydb->loadResult();
        return $result;
    }
    
    public function giveScore($userId,$shopId,$score){
        if(!$userId || !$shopId || !$score){
            return false;
        }
        $query = "select a.userId from `user_score` as a where a.userId='$userId' and a.shopId='$shopId'";
        $this->ssodb->setQuery($query);
        $result = $this->ssodb->loadResult();
        if($result){
            $query = "update user_score set scoreTotal=(scoreTotal+$score) where userId='$userId' and shopId='$shopId'";
        }else{
            $query = "insert user_score set userId='$userId',shopId='$shopId',scoreTotal=(scoreTotal+$score);";
        }
        
        return $this->ssodb->query($query);        
    }
    
    static public function getRules()
    {
    	$rules = '{
			"validation":[				
                                {
					"value":"mobile",
					"label":"手机号码",
					"rules":[
						{
	  						"name":"trim"
	  					},
						{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					},
	  					{
	  						"name":"regex",
                                                        "value":"/^0?(13[0-9]|15[012356789]|18[0236789]|14[57])[0-9]{8}$/",
                                                        "message":"%s%格式不正确"
	  					},
	 					{
	 						"name":"rangelength",
	 						"value":"[1,11]",
	 						"message":"%s%长度为1到11位"
	 					}
		  			]
				},
				{
					"value":"capture",
					"label":"验证码",
					"rules":[
						{
	  						"name":"trim"
	  					},
						{
	 						"name":"required",
	 						"message":"%s%为必填项"
	 					},
	  					{
	  						"name":"clearxss"
	  					}
		  			]
				}
			]
		}';
        
        return $rules;
    }

}
