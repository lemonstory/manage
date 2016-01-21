<?php
include_once '../controller.php';

class savehotrecommend extends controller
{
    public function action()
    {
        $action = $this->getRequest('action');
        $albumid = $this->getRequest('albumid');
        $tagids = $this->getRequest('tagids');
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
            // add
            $res = $manageobj->addRecommendHotDb($albumid);
        } else {
            // edit
            
        }
        
        if ($res == false) {
            $this->showErrorJson($manageobj->getError());
        }
        
        $recommenddescobj = new RecommendDesc();
        $recommenddescobj->addAlbumRecommendDescDb($albumid, $recommenddesc);
        
        $this->showSuccJson();
    }
}
new savehotrecommend();
?>