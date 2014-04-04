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
     * @param string $shopId
     * @return boolean|string
     */
    public function getAccessToken($shopId) {
        if(!$shopId){
            return false;
        }
        $accessToken = $this->redis->hGet("biz_$shopId", 'accessToken');
//        $redis->close();
        return $accessToken;
    }
    
    /**
     * 获取商家Redis accessToken值
     * @param string $shopId
     * @return boolean|string
     */
    public function setAccessToken($shopId,$accessToken) {
        if(!$shopId){
            return false;
        }
        $result = $this->redis->hSet("biz_$shopId", 'accessToken',$accessToken);
//        $redis->close();
        return $result;
    }
}
