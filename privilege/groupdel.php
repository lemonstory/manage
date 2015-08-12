<?php
include_once '../controller.php';

class addPrivilege extends controller
{
    public function action()
    {
        $id = $this->getRequest('id');
        $pObj = new ManagePrivilege();
        $ret = $pObj->deleteGroup($id);
        header('Location:/privilege/grouplist.php');
        exit;
    }
}
new addPrivilege();
?>