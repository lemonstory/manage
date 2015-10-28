<?php
include_once '../controller.php';

class setfocusstatus extends controller
{
    public function action()
    {
        $focusid = $this->getRequest('focusid', 0) + 0;
        $type = $this->getRequest("type", '');
        $status = $this->getRequest('status', 0);
        $ordernum = $this->getRequest('ordernum', 0);
        if (empty($focusid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        $updata = array();
        if ($type == 'status') {
            $updata['status'] = $status;
        }
        if ($type == 'ordernum') {
            $updata['ordernum'] = $ordernum;
        }
        
        if (!empty($updata)) {
            $manageobj = new ManageSystem();
            $result = $manageobj->updateFocusInfo($focusid, $updata);
            if(empty($result)) {
                $this->showErrorJson($manageobj->getError());
            }
        }
        
        $this->showSuccJson();
    }
}
new setfocusstatus();