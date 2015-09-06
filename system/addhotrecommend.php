<?php
include_once '../controller.php';

class addhotrecommend extends controller
{
    public function action()
    {
        $albumid = $this->getRequest('albumid');
        if (empty($albumid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        $albumobj = new Album();
        $albuminfo = $albumobj->get_album_info($albumid);
        
        $ordernum = 100;
        $manageobj = new ManageSystem();
        $manageobj->addRecommendHotDb($albumid, $ordernum);
        
        
        $smartyObj = $this->getSmartyObj();
        $smartyobj->assign('albuminfo', $albuminfo);
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('hotrecommendside', "active");
        $smartyObj->display("system/addhotrecommend.html"); 
    }
}
new addhotrecommend();
?>