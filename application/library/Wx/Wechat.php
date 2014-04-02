<?php

//define your token
define("TOKEN", "chihuobao365");

/*
 * 发送被动响应消息(回复)模板
 */
//文本类型模板
define("SEND_TPL_TEXT","<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[%s]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>");
//图文类型模板
define("SEND_TPL_NEWS_HEAD","<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[%s]]></MsgType>
<ArticleCount>%s</ArticleCount>
<Articles>
");
//多条消息循环
define("SEND_TPL_NEWS_ITEM","<item>
<Title><![CDATA[%s]]></Title> 
<Description><![CDATA[%s]]></Description>
<PicUrl><![CDATA[%s]]></PicUrl>
<Url><![CDATA[%s]]></Url>
</item>");
define("SEND_TPL_NEWS_FOOT","</Articles></xml>");

class Wx_Wechat {
   
    //开发者微信号
    private $_ToUserName;
    //发送方帐号（一个OpenID）
    private $_FromUserName;
    //消息创建时间 （整型）
    private $_CreateTime;
    //消息类型，event|text
    private $_MsgType;
    //事件类型，subscribe(订阅)、unsubscribe(取消订阅)
    private $_Event;
    //事件KEY值，与自定义菜单接口中KEY值对应
    private $_EventKey;
    
    private $_Content;
    private $_MsgId;

    //二维码的ticket，可用来换取二维码图片
    private $_Ticket;
    
    //上报地理位置
    private $_Latitude;
    private $_Longitude;
    private $_Precision;
    public $_postData;



    public function __construct() {
        $postStr = file_get_contents("php://input");
        $this->_postData=$postStr;
        if (!empty($postStr)) {
            $post_obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            
            $this->_ToUserName = $post_obj->ToUserName;
            $this->_FromUserName = $post_obj->FromUserName;
            $this->_CreateTime = $post_obj->CreateTime;
            $this->_MsgType = $post_obj->MsgType;
            $this->_Event = $post_obj->Event;
            $this->_EventKey = $post_obj->EventKey;
            $this->_Content = trim($post_obj->Content);
            $this->_MsgId = $post_obj->MsgId;
            
            $this->_Ticket = $post_obj->Ticket;
            //地理位置纬度
            $this->_Latitude = $post_obj->Latitude;
            //地理位置经度
            $this->_Longitude = $post_obj->Longitude;
            //地理位置精度
            $this->_Precision = $post_obj->Precision;
            
            
        }
    }
    
    /* 返回公众号id */

    public function getToUserName() {
        return $this->_ToUserName;
    }
    
    /* 返回用户openid */

    public function getFromUserName() {
        return $this->_FromUserName;
    }
    
      /* 返回获得消息类型 */

    public function getMsgType() {
        return (string) $this->_MsgType;
    }
    
    /* 返回获得事件类型 */

    public function getEvent() {
        return (string) $this->_Event;
    }
    
    /* 返回自定义菜单接口中KEY值 */

    public function getEventKey() {
        return (string) $this->_EventKey;
    }
    
    /* 返回消息内容 */
    public function getContent() {
        return $this->_Content;
    }

    /* 返回消息id */

    public function getMsgId() {
        return $this->_MsgId;
    }
    
    //返回经纬度
    public function getLonLat() {
        if($this->_Longitude && $this->_Latitude){
            $lonLat=$this->_Longitude.','.$this->_Latitude;
        }
        return $lonLat;
    }

    public function valid() {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    /**
     * 调试用
     */
    public function log($content = null,$fileName='xml.txt') {
//        die('9877');
        if ($content) {
            if(is_array($content)){
                $data=  json_encode($content);
            }
            $data = $content;
        } else {
            //set always_populate_raw_post_data enable_post_data_reading On
//        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
            $postStr = file_get_contents("php://input");
            $data = json_encode($postStr);
        }
        file_put_contents($fileName, $data);
    }
    

    //回复消息
    public function responseMsg($sendMsgType, $sendMsgContent) {
        //验证数据
        if($sendMsgType && $sendMsgContent){
            $time = time();
            switch ($sendMsgType) {
                case 'text':
                    if(is_string($sendMsgContent)){
                        $result = sprintf(SEND_TPL_TEXT,$this->_FromUserName,$this->_ToUserName,$time,$sendMsgType,$sendMsgContent);
                    }else{
                        $result='Text:Input something...';
                    }
                    break;
                
                case 'news':
                    if(is_array($sendMsgContent) && isset($sendMsgContent[0])){
                        $itemNum=count($sendMsgContent);
                        $result = sprintf(SEND_TPL_NEWS_HEAD,$this->_FromUserName,$this->_ToUserName,$time,$sendMsgType,$itemNum);
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
                            if(isset($sendMsgContent[$i]['picUrl']) && $sendMsgContent[$i]['picUrl']){
                                $picUrl=$sendMsgContent[$i]['picUrl'];
                            }else{
                                $picUrl='';
                            }
                            if(isset($sendMsgContent[$i]['url']) && $sendMsgContent[$i]['url']){
                                $url=$sendMsgContent[$i]['url'];
                            }else{
                                $url='';
                            }
                            $result .= sprintf(SEND_TPL_NEWS_ITEM, $sendMsgContent[$i]['title'], $sendMsgContent[$i]['description'], $sendMsgContent[$i]['picUrl'], $sendMsgContent[$i]['url']);
                        }
                        $result .= sprintf(SEND_TPL_NEWS_FOOT);
                    }else{
                        $result='News:Input something...';
                    }
                    break;    
                    
                    
                default:
                    $result='Input something...';
                    break;
            }
            $this->log($result);
            echo $result;
        }else{
            echo "";
        }
        exit;
    }
    

    private function checkSignature() {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
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

}
