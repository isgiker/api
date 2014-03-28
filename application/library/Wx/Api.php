<?php

//define
//发送客服消息(48小时内回复)模板
//发送文本消息模板
define("SEND_MSG_TPL_TEXT",'{
    "touser":"%s",
    "msgtype":"%s",
    "text":
    {
         "content":"%s"
    }
}');

//发送图文消息(图文消息条数限制在10条以内，注意，如果图文数超过10，则将会无响应。)
define("SEND_MSG_TPL_NEWS_HEAD",'{
    "touser":"%s",
    "msgtype":"%s",
    "news":{
        "articles": [
');

//多条消息循环
define("SEND_MSG_TPL_NEWS_ITEM",'{
             "title":"%s",
             "description":"%s",
             "url":"%s",
             "picurl":"%s"
}');

define("SEND_MSG_TPL_NEWS_FOOT",'         ]
    }
}');

class Wx_Api {
    
    /**
     * 获取access token 过期时间7200
     * @param string grant_type 获取access_token填写client_credential
     * @param string appid 第三方用户(服务公众号)唯一凭证
     * @param string secret 第三方用户(服务公众号)唯一凭证密钥，即appsecret
     * @return string
     */
    public function getAccessToken($appid,$secret) {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
        $data = $this->curl_Http_Request($url);
        $data = explode("\r\n", $data);
        $data = end($data);
        $data = json_decode($data, true);
        if (isset($data['errcode']) && $data['errcode']) {
            die("$data[errmsg]");
            $access_token = '';
        } else {
            $expires_in = $data['expires_in'];
            $access_token = $data['access_token'];
        }
        return $access_token;
    }
    
    /**
     * 自定义菜单创建接口
     * 目前自定义菜单最多包括3个一级菜单，每个一级菜单最多包含5个二级菜单。一级菜单最多4个汉字，二级菜单最多7个汉字，多出来的部分将会以“...”代替。
     * @param json $menu 菜单导航，参数说明：
     * button	 必要           一级菜单数组，个数应为1~3个
     * sub_button 否             二级菜单数组，个数应为1~5个
     * type	 必要           菜单的响应动作类型，目前有click、view两种类型
     * name	 必要           菜单标题，不超过16个字节，子菜单不超过40个字节
     * key	 click类型必须	菜单KEY值，用于消息接口推送，不超过128字节
     * url	 view类型必须	网页链接，用户点击菜单可打开链接，不超过256字节
     * $menu = '{
     *       "button": [
     *           {
     *               "type": "click", 
     *               "name": "@绑定手机", 
     *               "key": "KW_WX_BIND_MOBILE"
     *           }, 
     *           {
     *               "type": "click", 
     *               "name": "商家入驻", 
     *               "key": "KW_WX_BUSINESS_JOIN"
     *           }, 
     *           {
     *               "name": "餐饮", 
     *               "sub_button": [
     *                   {
     *                       "type": "view", 
     *                       "name": "点餐", 
     *                       "url": "http://m2.chihuobao365.com"
     *                   }, 
     *                   {
     *                       "type": "view", 
     *                       "name": "外卖", 
     *                       "url": "http://m2.chihuobao365.com"
     *                   }, 
     *                   {
     *                       "type": "click", 
     *                       "name": "订座", 
     *                       "key": "V1001_GOOD"
     *                   }
     *               ]
     *           }
     *       ]
     *   }';
     * @return json 成功返回{"errcode":0,"errmsg":"ok"}
     */
    public function createMenu($menu, $accessToken) {
        if(!trim($menu) || !trim($accessToken)){
            die('参数错误！');
        }
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$accessToken";

        //如果成功返回{"errcode":0,"errmsg":"ok"}
        $data = $this->curl_Http_Request($url, $menu, 'POST');
        return $data;
    }
    
    /**
     * 获取用户基本信息
     * @param type $openid 普通用户的标识，对当前公众号唯一
     * @param type $accessToken 调用接口凭证
     * @param type $lang 返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
     * @return json 成功返回用户信息的json数据
     * 返回参数说明：
     *  subscribe	 用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息。
     *  openid	 用户的标识，对当前公众号唯一
     *  nickname	 用户的昵称
     *  sex	 用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
     *  city	 用户所在城市
     *  country	 用户所在国家
     *  province	 用户所在省份
     *  language	 用户的语言，简体中文为zh_CN
     *  headimgurl	 用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空
     *  subscribe_time	 用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间
     */
    public function getUserInfo($openid,$accessToken,$lang='zh_CN'){
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken&openid=$openid&lang=$lang";

        //如果错误时返回{"errcode":40013,"errmsg":"invalid appid"}
        $data = $this->curl_Http_Request($url);
        return $data;
    }
    
    
    /**
     * 发送客服消息(48小时内回复)
     * @param string $sendMsgType 消息类型，text|news|image|voice|video|music
     * @param string|array $sendMsgContent
     * @param string $openid 普通用户openid
     * @param string $accessToken
     * @return json 成功返回{"errcode": 0,"errmsg": "ok"}
     */
    public function sendMsg($sendMsgType, $sendMsgContent, $openid, $accessToken) {
        //验证数据
        if($sendMsgType && $sendMsgContent && $openid && $accessToken){
            $time = time();
            switch ($sendMsgType) {
                case 'text':
                    if(is_string($sendMsgContent)){
                        $result = sprintf(SEND_MSG_TPL_TEXT,$openid,$sendMsgType,$sendMsgContent);
                    }
                    break;
                
                case 'news':
                    if(is_array($sendMsgContent) && isset($sendMsgContent[0])){
                        $itemNum=count($sendMsgContent);
                        $result = sprintf(SEND_MSG_TPL_NEWS_HEAD,$openid,$sendMsgType);
                        for ($i=0; $i <$itemNum; $i++) {
                            if(isset($sendMsgContent[$i]['title']) && $sendMsgContent[$i]['title']){
                                $title=$sendMsgContent[$i]['title'];
                            }else{
                                $title='';
                            }
                            if(isset($sendMsgContent[$i]['description']) && $sendMsgContent[$i]['description']){
                                $description=$sendMsgContent[$i]['description'];
                            }else{
                                $description='';
                            }
                            if(isset($sendMsgContent[$i]['url']) && $sendMsgContent[$i]['url']){
                                $url=$sendMsgContent[$i]['url'];
                            }else{
                                $url='';
                            }
                            if(isset($sendMsgContent[$i]['picUrl']) && $sendMsgContent[$i]['picUrl']){
                                $picUrl=$sendMsgContent[$i]['picUrl'];
                            }else{
                                $picUrl='';
                            }
                            if($i>0){
                                $comma=',';
                            }else{
                                $comma='';
                            }
                            //多条内容用逗号分隔
                            $result .= $comma;
                            $result .= sprintf(SEND_MSG_TPL_NEWS_ITEM, $sendMsgContent[$i]['title'], $sendMsgContent[$i]['description'], $sendMsgContent[$i]['url'], $sendMsgContent[$i]['picUrl']);
                        }
                        $result .= sprintf(SEND_TPL_NEWS_FOOT);
                    }
                    break;    
                    
                    
                default:
                    $result='';
                    break;
            }
            
            if(isset($result) && $result){
                $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$accessToken";

                $data = $this->curl_Http_Request($url,$result,'POST');
                return $data;
            }
            
        }
        return false;

    }
    
    static public function wxError($errcode) {
        switch ($errcode) {
            case -1:
                $errmsg = '系统繁忙';
                break;
            case 0:
                $errmsg = '请求成功';
                break;

            default:
                $errArray = array(
                    40008 => '不合法的消息类型',
                    40009 => '不合法的图片文件大小',
                    40010 => '不合法的语音文件大小',
                    40011 => '不合法的视频文件大小',
                    40012 => '不合法的缩略图文件大小',
                    40013 => '不合法的APPID',
                    40014 => '不合法的access_token',
                    40015 => '不合法的菜单类型',
                    40016 => '不合法的按钮个数',
                    40017 => '不合法的按钮个数',
                    40018 => '不合法的按钮名字长度',
                    40019 => '不合法的按钮KEY长度',
                    40020 => '不合法的按钮URL长度',
                    40021 => '不合法的菜单版本号',
                    40022 => '不合法的子菜单级数',
                    40023 => '不合法的子菜单按钮个数',
                    40024 => '不合法的子菜单按钮类型',
                    40025 => '不合法的子菜单按钮名字长度',
                    40026 => '不合法的子菜单按钮KEY长度',
                    40027 => '不合法的子菜单按钮URL长度',
                    40028 => '不合法的自定义菜单使用用户',
                    40029 => '不合法的oauth_code',
                    40030 => '不合法的refresh_token',
                    40031 => '不合法的openid列表',
                    40032 => '不合法的openid列表长度',
                    40033 => '不合法的请求字符，不能包含\uxxxx格式的字符',
                    40034 => '未知错误',
                    40035 => '不合法的参数',
                    40036 => '未知错误',
                    40037 => '未知错误',
                    40038 => '不合法的请求格式',
                    40039 => '不合法的URL长度',
                    40050 => '不合法的分组id',
                    40051 => '分组名字不合法',
                    41001 => '缺少access_token参数',
                    41002 => '缺少appid参数',
                    41003 => '缺少refresh_token参数',
                    41004 => '缺少secret参数',
                    41005 => '缺少多媒体文件数据',
                    41006 => '缺少media_id参数',
                    41007 => '缺少子菜单数据',
                    41008 => '缺少oauth code',
                    41009 => '缺少openid',
                    42001 => 'access_token超时',
                    42002 => 'refresh_token超时',
                    42003 => 'oauth_code超时',
                    43001 => '需要GET请求',
                    43002 => '需要POST请求',
                    43003 => '需要HTTPS请求',
                    43004 => '需要接收者关注',
                    43005 => '需要好友关系',
                    44001 => '多媒体文件为空',
                    44002 => 'POST的数据包为空',
                    44003 => '图文消息内容为空',
                    44004 => '文本消息内容为空',
                    45001 => '多媒体文件大小超过限制',
                    45002 => '消息内容超过限制',
                    45003 => '标题字段超过限制',
                    45004 => '描述字段超过限制',
                    45005 => '链接字段超过限制',
                    45006 => '图片链接字段超过限制',
                    45007 => '语音播放时间超过限制',
                    45008 => '图文消息超过限制',
                    45009 => '接口调用超过限制',
                    45010 => '创建菜单个数超过限制',
                    45015 => '回复时间超过限制',
                    45016 => '系统分组，不允许修改',
                    45017 => '分组名字过长',
                    45018 => '分组数量超过上限',
                    46001 => '不存在媒体数据',
                    46002 => '不存在的菜单版本',
                    46003 => '不存在的菜单数据',
                    46004 => '不存在的用户',
                    47001 => '解析JSON/XML内容错误',
                    48001 => 'api功能未授权',
                    50001 => '用户未授权该api'
                );
                if (isset($errArray[$errcode]) && $errArray[$errcode]) {
                    $errmsg = $errArray[$errcode];
                } else {
                    $errmsg = '未知错误';
                }
                return $errcode . ' ' . $errmsg;
                break;
        }
    }
    
    public function curl_Http_Request($url, $data = null, $method = 'GET') {
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
