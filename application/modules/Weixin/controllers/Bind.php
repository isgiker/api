<?php

/**
 * @name BindController
 * @author Vic
 * @desc 微信接口绑定手机号
 */
class BindController extends Core_Basic_Controllers {

    private $wxApi;
    private $model;
    private $redisModel;

    public function init() {
        $this->wxApi = new Wx_Api();
        $this->model = new Weixin_ApiModel();
        $this->redisModel = new RedisModel();
        $this->getView()->assign('_view', $this->getView());
    }

    public function mobileAction() {
        $rules = $this->model->getRules();
        $this->getView()->assign("rules", json_decode($rules)->validation);
        if ($this->getRequest()->isPost()) {
            $mobile = @trim($_POST['mobile']);
            $openid = @trim($_GET['openid']);
            $wxid = @trim($_GET['wxid']);

            $shopId=  $this->model->getShopId($wxid);
            $userId=  $this->model->getUserId($mobile);

            $v = new validation(); //数据校验
            $v->validate($rules, $_POST);
            if (!empty($v->error_message)) {
                $this->getView()->assign("error", $v->error_message); //输出同步错误信息
                return true;
            }

            //验证篡改数据
            $sign = @trim($_GET['sign']);
            if ($sign != sha1($wxid . '@' . $openid)) {
                $errCode = '50001';
            }
            if (!isset($errCode)) {
                if ($wxid) {
                    //获取token
                    $wxAppInfo = $this->model->getWxappInfo($wxid);
                    //如果缓存过期或无数据则调用微信api
                    $accessToken = $this->redisModel->getAccessToken($shopId);
                    if(!$accessToken){
                        $accessToken = $this->wxApi->getAccessToken($wxAppInfo['appId'], $wxAppInfo['appSecret']);
                        //写入缓存
                        $this->redisModel->setAccessToken($shopId,$accessToken);                        
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
                                                        
                            if ($shopId) {
                                $isOpen = $this->model->scoreIsOpen($shopId);
                                if ($isOpen) {
                                    //获取店铺赠送积分
                                    $score=$this->model->getSubscribeScore($shopId);
                                    if(!$score){
                                        $score=0;
                                    }
                                    //赠送积分
                                    $this->model->giveScore($userId,$shopId,$score);
                                }
                            }
                        }
                    }
                } else {
                    $errCode = '50005';
                }
            }
            $this->getView()->assign("errCode", $errCode);
        }
    }
    
    public function testAction() {
        $redis=Factory::getRedisDBO();
        $v=$redis->hGet('testhashss', 'key1');
        echo $v;
        $redis->close();
        
        return false;
    }

}
