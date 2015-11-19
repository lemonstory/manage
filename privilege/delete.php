<?php
include_once '../controller.php';

class delPrivilege extends controller
{
    public function action()
    {
        $pid = $this->getRequest('pid');
        if (empty($pid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        $pObj = new ManagePrivilege();
        $pObj->deletePrivilege($pid);
        $this->showSuccJson();
    }
}
new delPrivilege();
?>