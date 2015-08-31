<?php
include_once '../controller.php';

class groupdel extends controller
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
new groupdel();
?>