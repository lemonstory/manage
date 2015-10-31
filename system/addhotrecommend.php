<?php
include_once '../controller.php';

class addhotrecommend extends controller
{
    public function action()
    {
        die();
        
        $albumid = $this->getRequest('albumid');
        if (empty($albumid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        $albumobj = new Album();
        $albuminfo = $albumobj->get_album_info($albumid);
        if (empty($albuminfo)) {
            $this->showErrorJson(ErrorConf::albumInfoIsEmpty());
        }
        
        $refer = "";
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $refer = $_SERVER['HTTP_REFERER'];
        }
        $smartyObj = $this->getSmartyObj();
        $smartyobj->assign('albuminfo', $albuminfo);
        $smartyobj->assign('refer', $refer);
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('hotrecommendside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("system/addhotrecommend.html"); 
    }
}
new addhotrecommend();
?>