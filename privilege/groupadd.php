<?php
include_once '../controller.php';

class addPrivilege extends controller
{
    public function action()
    {
        $name = $this->getRequest('name');
        $desc = $this->getRequest('desc');
        $pObj = new ManagePrivilege();
        $ret = $pObj->addGroup($name, $desc);
        header('Location:/privilege/grouplist.php');
        exit;
    }
}
new addPrivilege();
?>