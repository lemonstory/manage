<?php
include_once '../controller.php';

class sethotrecommendstatus extends controller
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
        
        $managesysobj = new ManageSystem();
        $result = $managesysobj->updateRecommendStatusByIds('share_main', 'recommend_new_online', $albumid, $status);
        if(empty($result)) {
            $this->showErrorJson($managesysobj->getError());
        }
        
        $this->showSuccJson();
    }
}
new sethotrecommendstatus();