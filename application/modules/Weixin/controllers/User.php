<?php

/**
 * @name UserController
 * @author Vic
 * @desc 微信用户管理接口
 */
class UserController extends Core_Basic_Controllers {

    public function init() {
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
    }

    /**
     * 获取access token 过期时间7200
     * @param string grant_type 获取access_token填写client_credential
     * @param string appid 第三方用户(服务公众号)唯一凭证
     * @param string secret 第三方用户(服务公众号)唯一凭证密钥，即appsecret
     * @return string
     */
    public function getAccessTokenAction() {
        //这两个参数需要根据shopId获取
        $appid = 'wx1d291887f0e758ab';
        $secret = '25d8f95f363ed0a105e1f27fe0ced69d';
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
        $data = Util::curl_Http_Request($url);
        $data = explode("\r\n", $data);
        $data = end($data);
        $data = json_decode($data, true);
        if (isset($data['errcode']) && $data['errcode']) {
            echo $data['errmsg'];
            $access_token = '';
        } else {
            $expires_in = $data['expires_in'];
            $access_token = $data['access_token'];
        }
        echo $access_token;
        return $access_token;
    }

    /**
     * 自定义菜单创建接口
     * 目前自定义菜单最多包括3个一级菜单，每个一级菜单最多包含5个二级菜单。一级菜单最多4个汉字，二级菜单最多7个汉字，多出来的部分将会以“...”代替。
     * @param json $menu 菜单导航
     */
    public function createMenuAction() {
        
        /*
          button	 必要           一级菜单数组，个数应为1~3个
          sub_button 否             二级菜单数组，个数应为1~5个
          type	 必要           菜单的响应动作类型，目前有click、view两种类型
          name	 必要           菜单标题，不超过16个字节，子菜单不超过40个字节
          key	 click类型必须	菜单KEY值，用于消息接口推送，不超过128字节
          url	 view类型必须	网页链接，用户点击菜单可打开链接，不超过256字节
         */
        $menu = '{
    "button": [
        {
            "type": "click", 
            "name": "@绑定手机", 
            "key": "KW_WX_BIND_MOBILE"
        }, 
        {
            "type": "click", 
            "name": "商家入驻", 
            "key": "KW_WX_BUSINESS_JOIN"
        }, 
        {
            "name": "餐饮", 
            "sub_button": [
                {
                    "type": "view", 
                    "name": "点餐", 
                    "url": "http://m2.chihuobao365.com"
                }, 
                {
                    "type": "view", 
                    "name": "外卖", 
                    "url": "http://m2.chihuobao365.com"
                }, 
                {
                    "type": "click", 
                    "name": "订座", 
                    "key": "V1001_GOOD"
                }
            ]
        }
    ]
}';
//        $accessToken = getAccessToken();
        $accessToken = 'ed4nvUe0eZBqeFHVqNrTqHuM1_eIHBvvFS6trDY_RBTHN-9ABdi_cJUyCwA5kUps5DV_nfl3rbTU9neDHAef2mNqmjw9TAIdVlLVuIXv29-LgMYcDApZC0MY8lx0lZ2LeSMQ-zxQ1KUrx3xXomsppQ';
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$accessToken";

        //如果成功返回{"errcode":0,"errmsg":"ok"}
        $data = Util::curl_Http_Request($url, $menu, 'POST');
        $data = json_decode($data, true);
        if ($data['errcode'] == 0 && $data['errmsg'] == 'ok') {
            echo 'success';
        } else {
            //如果是access_token超时
            if ($data['errcode'] == '42001') {
                //获取新的access_token，然后重新请求接口；
            } else {
                $errmsg = $data['errmsg'];
                die($errmsg);
            }
        }
        return $data;
    }

}
