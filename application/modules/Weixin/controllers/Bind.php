<?php

/**
 * @name BindController
 * @author Vic
 * @desc 微信接口绑定手机号
 */
class BindController extends Core_Basic_Controllers {
    private $apiConfig;
    private $wxApi;
    private $model;
    private $redisModel;

    public function init() {
        $this->wxApi = new Wx_Api();
        $this->model = new Weixin_ApiModel();
        $this->redisModel = new RedisModel();
        $this->getView()->assign('_view', $this->getView());
        
        $this->apiConfig = new Yaf_Config_Ini(CONFIG_PATH . DS . 'api.ini');
    }

//    if(strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')){
//            sleep(2);
//             echo "<script>self.location='http://www.baidu.com';</script>";
//    }
    public function mobileAction() {
        $rules = $this->model->getRules();
        $this->getView()->assign("rules", json_decode($rules)->validation);
        
        $wxid = @trim($_GET['wxid']);
        $this->getView()->assign("wxid", $wxid);
        
        if ($this->getRequest()->isPost()) {
            $mobile = @trim($_POST['mobile']);
            $capture = @trim($_POST['capture']);
            $openid = @trim($_GET['openid']);
            $this->getView()->assign("mobile", $mobile);
            $this->getView()->assign("capture", $capture);
            
            $shopId=  $this->model->getShopId($wxid);
//            $userId=  $this->model->getUserId($mobile);

            $v = new validation(); //数据校验
            $v->validate($rules, $_POST);
            if (!empty($v->error_message)) {
                $error=$v->error_message;
                $this->getView()->assign("error", $error); //输出同步错误信息
                return true;
            }
            
            if (!$this->checkPhoneVerify($mobile, $capture)) {
                $error='验证码错误或已过期!';
                $this->getView()->assign("error", $error);
            }

            //验证篡改数据
//            $sign = @trim($_GET['sign']);
//            if ($sign != sha1($wxid . '@' . $openid)) {
//                $errCode = '50001';
//            }
            if (!isset($errCode) && !isset($error)) {
                if ($wxid) {
                    //获取token
                    $wxAppInfo = $this->model->getWxappInfo($wxid);
                    //如果缓存过期或无数据则调用微信api
                    $accessToken = $this->redisModel->getAccessToken($wxid);
                    if(!isset($accessToken) || !$accessToken){
                        $accessToken = $this->wxApi->getAccessToken($wxAppInfo['appId'], $wxAppInfo['appSecret']);
                        //写入缓存
                        $this->redisModel->setAccessToken($wxid,$accessToken);                        
                    }
                    
                } else {
                    $errCode = '50002';
                }
            }
            if (!isset($errCode)) {
                if (isset($accessToken) && $accessToken && $openid) {
                    $wxUserInfo = $this->wxApi->getUserInfo($openid, $accessToken);
                    $wxUserInfo = json_decode($wxUserInfo, true);
                } else {
                    $errCode = '50003';
                }
            }
            if (!isset($errCode)) {
                if (isset($wxUserInfo['nickname']) && $wxUserInfo['nickname'] && $mobile) {
                    //检查是否绑定过
                    $checkResult = $this->model->checkBind($wxid, $mobile);
                    if ($checkResult['fansId']) {
                        //无需绑定
                        $errCode = 201;
                    } else {
                        if (!$this->model->bind($wxid, $mobile, $wxUserInfo)) {
                            //绑定失败
                            $errCode = '50004';
                        } else {
                            //绑定成功
                            $errCode = 200;
                            
                            //如果商家开启了会员积分功能模块，首次关注微信公众号赠送积分（由商家设置）
                                                        
//                            if ($shopId) {
//                                $isOpen = $this->model->scoreIsOpen($shopId);
//                                if ($isOpen) {
//                                    //获取店铺赠送积分
//                                    $score=$this->model->getSubscribeScore($shopId);
//                                    if(!$score){
//                                        $score=0;
//                                    }
//                                    //赠送积分
//                                    $this->model->giveScore($userId,$shopId,$score);
//                                }
//                            }
                        }
                    }
                } else {
                    $errCode = '50005';
                }
            }
            $this->getView()->assign("errCode", $errCode);
        }
        
        
        
    }
    
    /**
     * 检测手机验证码
     * @param  integer $mobile 手机号
     * @param  integer $capture   短信验证码
     * @return boolean false:验证失败|true:验证成功
     */
    protected function checkPhoneVerify($mobile, $capture) {
        $model = new CommonModel();
        $checkResult = $model->checkPhoneVerify($mobile, $capture);
        if (!$checkResult) {
            return false;
        }
        //检查提交时间是否过期
        $expire = $this->apiConfig['sms']['wxBindMobile']['expire'];
        $time = time() - $checkResult['datetime'];
        if ($time > $expire) {
            return false;
        }
        
        return true;
    }
    
    public function testAction() {
        $redis=Factory::getRedisDBO();
        $v=$redis->hGet('testhashss', 'key1');
        echo $v;
        $redis->close();
        
        return false;
    }

}
