<?php
include_once '../controller.php';

class groupdel extends controller
{
    public function action()
    {
        $groupid = $this->getRequest('groupid');
        if (empty($groupid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        $pObj = new ManagePrivilege();
        $ret = $pObj->deleteGroup($groupid);
        $this->showSuccJson();
    }
}
new groupdel();
?>