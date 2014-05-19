<?php

/**
 * @name CommonController
 * @author Vic(shiwei)
 * @desc 发送信息接口
 */
class CommonController extends Core_Basic_Controllers {

    private $apiConfig;
    private $model;

    public function init() {
        Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        $this->apiConfig = new Yaf_Config_Ini(CONFIG_PATH . DS . 'api.ini');

        $this->model = new CommonModel();
    }

    /**
     * 发送手机短信
     * @param type $param
     */
    public function sendSmsAction() {
        if ($this->isAjax()) {
            $mobile = $_POST['mobile'];
            $wxid = $_POST['wxid'];
            $type = $_POST['type'];
            $pattern = '/^([0-9]{11})?$/';

            if(!$mobile){
                $this->err(null, '请输入正确的手机号码！');
            }elseif (preg_match($pattern, $mobile)) {
                //生成验证码和短信内容
                $capture = $this->getCaptureCode($this->apiConfig['sms'][$type]['length']);
                $content = $this->apiConfig['sms'][$type]['content'];
                $content = preg_replace('/{capture}/', $capture, $content);

                $apiModel = new Weixin_ApiModel();
                if ($apiModel->checkBind($wxid, $mobile)) {
                    $this->err(null, '您的手机已经绑定过了，无需再次绑定!');
                }

                $time = $this->checkResend($mobile);
                if ($time) {
                    $this->err(null, '客官，先喝杯茶歇会。' . $time[0] . '分' . $time[1] . '秒后再获取验证码。');
                }
                
                $val = $this->sendSms($mobile, $content);
                $codeD = array(
                    'mobile' => $mobile,
                    'capture' => $capture,
                    'type' => $type,
                    'content' => $content,
                    'status' => $val,
                    'used' => 1,
                    'datetime' => time()
                );
                if($this->model->recordSendedSms($codeD)){
                    if ($val > 0) {
                        $this->ok(null, null, '手机验证码已发送，请查看手机!');
                    } else {
                        $this->err(null, '验证码发送失败!');
                    };
                }

            } else {
                $this->err(null, '手机号格式不正确!');
            }
        }
        exit();
    }

    public function checkResend($mobile) {
        $time = time() - 180;
        $datetime = $this->model->checkResend($mobile, $time);
        if ($datetime) {
            $s = 180 - (time() - $datetime);
            $m = intval($s / 60);
            $s = $s / 60 - $m;
            $s = $s * 60;
            return [$m, $s];
        } else {
            return false;
        }
    }

}
