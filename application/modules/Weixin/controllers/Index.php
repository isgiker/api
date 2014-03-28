<?php

/**
 * @name IndexController
 * @author Vic
 * @desc 微信接口初始化
 */
class IndexController extends Core_Basic_Controllers {

    public function init() {
        
    }

    public function indexAction() {
        $wechatObj = new Wx_Wechat();
        //微信原生接口URL验证时取消本行注释，验证完后再注释掉
//        $wechatObj->valid();
//        exit;

        $msgType = $wechatObj->getMsgType();
        //消息类型
        switch ($msgType) {
            //事件
            case 'event':
                $event = $wechatObj->getEvent();
                switch ($event) {
                    //自定义菜单事件
                    case 'CLICK':
                        $eventKey = $wechatObj->getEventKey();
                        //“绑定手机号”菜单
                        if ($eventKey == 'KW_WX_BIND_MOBILE') {
                            //回复消息
                            $fromUsername = $wechatObj->getFromUserName();
                            $toUsername = $wechatObj->getToUserName();
                            $sign=sha1($toUsername.'@'.$fromUsername);
                            $content = '欢迎您绑定手机号，轻松绑定后即可享受VIP优惠价格。';
                            $content.="\n";
                            $content.='<a href="http://114.241.25.22/Weixin/Bind/mobile?wxid=' . $toUsername . '&openid=' . $fromUsername . '&sign='.$sign.'"> 点击这里，立即绑定 </a>';
                            $wechatObj->responseMsg('text', $content);
                        } elseif ($eventKey == 'KW_WX_BUSINESS_JOIN') {
                            //图文Demo
                            $content = array();
                            $content[0]['title'] = '商家入驻资格说明1';
                            $content[0]['description'] = '以后完善..........................................。';
                            $content[0]['picUrl'] = 'http://ww1.sinaimg.cn/bmiddle/75a81cc4jw1eet0rwj5yvj209q06gdfx.jpg';
                            $content[0]['url'] = 'http://www.chihuobao365.com';
                            $content[1]['title'] = '商家入驻资格说明2';
                            $content[1]['description'] = '以后完善..........................................。';
                            $content[1]['picUrl'] = 'http://ww1.sinaimg.cn/bmiddle/75a81cc4jw1eet0rwj5yvj209q06gdfx.jpg';
                            $content[1]['url'] = 'http://www.chihuobao365.com';
                            $wechatObj->responseMsg('news', $content);
                        }
                        break;
                    case 'LOCATION':
                        $lonLat = $wechatObj->getLonLat();
                        //获取微信用户id，把openid、经纬度存储到数据库。自定义菜单里带上openid参数，当用户访问微网站的时候根据此参数获取地理位置。
                        $fromUsername = $wechatObj->getFromUserName();
                        $wechatObj->log($lonLat);
                        break;



                    default:
                        break;
                }

                break;
            /*接收普通消息--------------------------------------------begin*/
            //普通文本消息
            case 'text':
                $content = $wechatObj->getContent();
                //关键词回复;
                switch ($content) {
                    case 'wifi':
                        $text = 'wifi密码是：1314888';
                        $wechatObj->responseMsg('text', $text);
                        break;
                }
                break;
            default:
                break;
        }

        return false;
    }

}
