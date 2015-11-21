<?php
include_once '../controller.php';

class setsearchcontentstatus extends controller
{
    public function action()
    {
        $id = $this->getRequest('id', 0) + 0;
        $status = $this->getRequest('status', 0);
        if (empty($id) || empty($status)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $updata['status'] = $status;
        $searchobj = new SearchCount();
        $result = $searchobj->updateSearchContentCountInfo($id, $updata);
        if(empty($result)) {
            $this->showErrorJson($searchobj->getError());
        }
        
        $this->showSuccJson();
    }
}
new setsearchcontentstatus();