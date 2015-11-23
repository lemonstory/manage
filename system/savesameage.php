<?php
include_once '../controller.php';

class savesameage extends controller
{
    public function action()
    {
        $action = $this->getRequest('action');
        $albumid = $this->getRequest('albumid');
        $agetype = $this->getRequest('agetype');
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
            $res = $manageobj->addRecommendSameAgeDb($albumid, $agetype);
        } else {
            // edit
            $data = array("agetype" => $agetype);
            $res = $manageobj->updateSameAgeInfoByIds($albumid, $data);
        }
        if ($res == false) {
            $this->showErrorJson($manageobj->getError());
        }
        $this->showSuccJson();
    }
}
new savesameage();
?>