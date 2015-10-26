<?php
include_once '../controller.php';

class setfocusstatus extends controller
{
    public function action()
    {
        $uid = $this->getUid();
        $focusid = $this->getRequest('focusid', 0) + 0;
        $status = $this->getRequest('status', 0);
        if (empty($uid)) {
            $this->showErrorJson(ErrorConf::noLogin());
        }
        if (empty($focusid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $manageobj = new ManageSystem();
        $result = $manageobj->updateFocusInfo($focusid, array("status" => $status));
        if(empty($result)) {
            $this->showErrorJson($manageobj->getError());
        }
        
        $this->showSuccJson();
    }
}
new setfocusstatus();