<?php
include_once '../controller.php';

class setsameagestatus extends controller
{
    public function action()
    {
        $albumid = $this->getRequest('albumid', 0) + 0;
        $status = $this->getRequest('status', 0);
        if (empty($albumid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $albumobj = new Album();
        $albuminfo = $albumobj->get_album_info($albumid);
        if (empty($albuminfo)) {
            $this->showErrorJson(ErrorConf::albumInfoIsEmpty());
        }
        
        $managelistenobj = new ManageListen();
        $result = $managelistenobj->updateSameAgeStatusByIds($albumid, $status);
        if(empty($result)) {
            $this->showErrorJson($managelistenobj->getError());
        }
        
        $this->showSuccJson();
    }
}
new setsameagestatus();