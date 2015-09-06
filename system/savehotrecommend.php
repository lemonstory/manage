<?php
include_once '../controller.php';

class savehotrecommend extends controller
{
    public function action()
    {
        $albumid = $this->getRequest('albumid');
        if (empty($albumid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        $albumobj = new Album();
        $albuminfo = $albumobj->get_album_info($albumid);
        if (empty($albuminfo)) {
            $this->showErrorJson(ErrorConf::albumInfoIsEmpty());
        }
        
        $manageobj = new ManageSystem();
        $res = $manageobj->addRecommendHotDb($albumid);
        if ($res == false) {
            $this->showErrorJson($manageobj->getError());
        }
        $this->showSuccJson();
    }
}
new savehotrecommend();
?>