<?php

/**
 * @name IndexController
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends Yaf_Controller_Abstract {

    private $_layout;

    public function init() {
        /* use layout */
        $this->_layout = Yaf_Registry::get("layout");
    }

    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/sample/index/index/index/name/root 的时候, 你就会发现不同
     */
    public function indexAction($name = "Stranger") {
        
        die('13413999');
        //1. fetch query
        $get = $this->getRequest()->getQuery("get", "default value");

        //2. fetch model
        $model = new SampleModel();

        //3. assign
        $this->getView()->assign("content", $model->helloSample());
        $this->getView()->assign("name", $name);
        $this->getView()->assign("data", $model->dbSample());

        $this->_layout->meta_title = 'Yaf-J Framework Hello World!';
        $this->_layout->loadLayout();

        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        return TRUE;
    }
    
    public function mongoAction(){
        $model = new SampleModel();
        $model->mongoSample();
    }

    public function uploadAction() {
        $imagesConfig = Yaf_Registry::get("imagesConfig");
        $imagesServerGroups = $imagesConfig->common->setting->images->serverGroup;
        $fi = new File_Image();
        $servGroup = $fi->getImageServerGroup($imagesServerGroups);
        $path = $fi->getImagePath('800X600', 'png', $servGroup);
        

        $config = array('hostname' => '110.18.243.6', 'username' => 'imagesftp', 'password' => 'tj365imagesftp', 'port' => '21');
        $ftp = new File_Ftp();
        $ftp->connect($config);
        
        $ftp->createFolder($path['filePath']);
        $ftp->upload('D:/var/www/php/_tchg/static/img/admin/busniess_img.png', $path['filePath'] . '/' . $path['fileName'], $mode = 'auto', $permissions = 777);
        
        return false;
    }
    
    public function imgurlAction(){
        $imagesConfig = Yaf_Registry::get("imagesConfig");
        $fi = new File_Image();
        $imgParameter = array('imgSize'=>'2','sizeDesc'=>'medium','imgUrl'=>'2013-04-25/upload_72667_1366833257.jpg|/images/uploads/2013-04-25/thumb/general_2013042503541713668332571511.jpg|/images/uploads/2013-04-25/thumb/thumb_2013042503541713668332571511.jpg');
        $result = $fi->generateImgUrl($imgParameter,$imagesConfig);
        print_r($result);
        exit;
    }
    
    public function testAction(){
        $fenweiConfig = new Yaf_Config_Ini(CONFIG_PATH . DS . 'texttable.ini','canyin_shop_fenwei');
        $fenweiTable = $fenweiConfig->toArray();
        print_r($fenweiTable);exit;
    }

}
