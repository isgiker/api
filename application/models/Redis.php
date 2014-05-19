<?php
/**
 * @name RedisModel
 * @desc Redis数据类
 * @author vic(shiwei)
 */
class RedisModel {
    
    protected $redis;
    
    public function __construct() {
        $this->redis=Factory::getRedisDBO();
    }
    
    /**
     * 获取商家Redis accessToken值
     * @param string $wxid
     * @return boolean|string
     */
    public function getAccessToken($wxid) {
        if(!$wxid){
            return false;
        }
        $accessToken = $this->redis->get("accessToken_$wxid");
//        $redis->close();
        return $accessToken;
    }
    
    /**
     * 获取商家Redis accessToken值
     * @param string $wxid
     * @return boolean|string
     */
    public function setAccessToken($wxid,$accessToken) {
        if(!$wxid || !$accessToken){
            return false;
        }
        $accessToken = json_decode($accessToken, true);
        if (isset($accessToken['access_token']) && $accessToken['access_token']) {
            $result = $this->redis->set("accessToken_$wxid", $accessToken['access_token']);
            if ($result) {
                $this->redis->expire("accessToken_$wxid", $accessToken['expires_in'] - 600);
            }
            return $result;
        }
        
//        $redis->close();
        return false;
    }
}
