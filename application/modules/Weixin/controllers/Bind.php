<?php

/**
 * @name BindController
 * @author Vic
 * @desc 微信接口绑定手机号
 */
class BindController extends Core_Basic_Controllers {

    private $wxApi;
    private $model;

    public function init() {
        $this->wxApi = new Wx_Api();
        $this->model = new Weixin_ApiModel();
    }

    public function mobileAction() {        
        if ($this->getRequest()->isPost()) {
            $mobile = @trim($_POST['mobile']);
            $openid = @trim($_GET['openid']);
            $wxid = @trim($_GET['wxid']);
            //验证篡改数据
            $sign=@trim($_GET['sign']);
            if($sign!=sha1($wxid.'@'.$openid)){
                $errCode='50001';
            }
            if (!isset($errCode)) {
                if ($wxid) {
                    //获取token
                    $wxAppInfo = $this->model->getWxappInfo($wxid);
                    $accessToken = $this->wxApi->getAccessToken($wxAppInfo['appId'], $wxAppInfo['appSecret']);
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
                        }
                    }
                } else {
                    $errCode = '50005';
                }
            }
            $this->getView()->assign("errCode", $errCode);
        }
        
    }

}
