<?php
include_once '../controller.php';

class delPrivilege extends controller
{
    public function action()
    {
        $pid = $this->getRequest('id');
        $pObj = new ManagePrivilege();
        $pObj->deletePrivilege($pid);
        header('Location:/privilege/list.php');
        exit;
    }
}
new delPrivilege();
?>