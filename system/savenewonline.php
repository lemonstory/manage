<?php
include_once '../controller.php';

class savenewonline extends controller
{
    public function action()
    {
        $action = $this->getRequest('action');
        $albumid = $this->getRequest('albumid');
        $agetype = $this->getRequest('agetype');
        $recommenddesc = $this->getRequest('recommenddesc', "");
        if (empty($albumid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $albumobj = new Album();
        $albuminfo = $albumobj->get_album_info($albumid);
        if (empty($albuminfo)) {
            $this->showErrorJson(ErrorConf::albumInfoIsEmpty());
        }
        
        $manageobj = new ManageSystem();
        if (empty($action)) {
            $res = $manageobj->addRecommendNewOnlineDb($albumid, $agetype);
        } else {
            $data = array("agetype" => $agetype);
            $res = $manageobj->updateNewOnlineInfoByIds($albumid, $data);
        }
        if ($res == false) {
            $this->showErrorJson($manageobj->getError());
        }
        
        // 添加推荐语
        $recommenddescobj = new RecommendDesc();
        $recommenddescobj->addAlbumRecommendDescDb($albumid, $recommenddesc);
        
        $this->showSuccJson();
    }
}
new savenewonline();
?>