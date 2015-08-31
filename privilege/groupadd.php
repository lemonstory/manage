<?php
include_once '../controller.php';

class groupadd extends controller
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
new groupadd();
?>