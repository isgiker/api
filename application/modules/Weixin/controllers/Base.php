<?php

/**
 * @name BaseController
 * @author Vic
 * @desc 微信接口
 */
class BaseController extends Core_Basic_Controllers {
    private $wxApi;
    private $model;
    public function init() {
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->wxApi=new Wx_Api();
        $this->model=new Weixin_ApiModel();
    }

    /**
     * 获取access token 过期时间7200
     * @param string grant_type 获取access_token填写client_credential
     * @param string appid 第三方用户(服务公众号)唯一凭证
     * @param string secret 第三方用户(服务公众号)唯一凭证密钥，即appsecret
     * @return string
     */
    public function getAccessTokenAction() {
        //这两个参数需要根据shopId或微信id获取
        $appid = 'wx1d291887f0e758ab';
        $secret = '25d8f95f363ed0a105e1f27fe0ced69d';
        $accessToken=$this->wxApi->getAccessToken($appid, $secret);
        print_r($accessToken);
    }

    /**
     * 自定义菜单创建接口
     * 目前自定义菜单最多包括3个一级菜单，每个一级菜单最多包含5个二级菜单。一级菜单最多4个汉字，二级菜单最多7个汉字，多出来的部分将会以“...”代替。
     * @param json $menu 菜单导航
     * @param string $wxid 根据微信id获取appid和secret
     * @example http://114.241.25.22/weixin/Base/createMenu?wxId=gh_dd6146293b45
     */
    public function createMenuAction() {
//        $wxid=$_GET['wxid'];
//        $wxAppInfo = $this->model->getWxappInfo($wxid);
//        $accessToken=$this->wxApi->getAccessToken($wxAppInfo['appId'], $wxAppInfo['appSecret']);


$menu = '{
    "button": [
        {
            "type": "click", 
            "name": "绑定手机号", 
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
//        $accessToken = $this->getAccessToken();
        $accessToken = '6qrkyp924nozlS17NHDgvpZ5L76ECr2_iNqsdz-9Lr3-gDsqEgUmz2NTsMsK380drCpdAg3TlghODQupNwS4JcfzZNFn-sgWzeF66vZuuHwE9WjOvy63TJlHnloJfhbUnoTKIi25F4M9IQQA8N1wrQ';
        $data = $this->wxApi->createMenu($menu, $accessToken);
        $data = json_decode($data, true);
        if ($data['errcode'] == 0 && $data['errmsg'] == 'ok') {
            die($data['errmsg']);
        } else {
            //如果是access_token超时
            if ($data['errcode'] == '42001') {
                //获取新的access_token，然后重新请求接口；
            } else {
                $errmsg = $data['errmsg'];
                die($errmsg);
            }
        }
    }
    
    //发送客服消息测试
    public function sendMsgAction(){
        $sendMsgType='text';
        $sendMsgContent='Hello!';
        $openid='oLSypjjc5KLXigZC6-IeCiW139NM';
        $accessToken='11zI0KCwj8Ex1cJ7u5sHUfJpbfCHTKEegy8kJWUBsn8LEoA9OO4GBcMGq33CDK-aMA7GHaqlXgUpOG6GW9xsUGgmZBb5z8MuMc8pv1a_VERmd4i27rkLjo0SsSlPCOpOeY1Me_a-baK_w9U1vlKISeCg';
        $data = $this->wxApi->sendMsg($sendMsgType, $sendMsgContent, $openid, $accessToken);
        print_r($data);
        $data = json_decode($data, true);
                    if($data['errcode']=='40001'){
                        die("999");
                    }
    }

}
