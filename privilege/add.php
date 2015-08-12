<?php
include_once '../controller.php';

class addPrivilege extends controller
{
    public function action()
    {
        $action = $this->getRequest('action');
        $desc = $this->getRequest('desc');
        $pObj = new ManagePrivilege();
        $ret = $pObj->addPrivilege($action, $desc);
        header('Location:/privilege/list.php');
        exit;
    }
}
new addPrivilege();
?>