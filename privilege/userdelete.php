<?php
include_once '../controller.php';

class addPrivilege extends controller
{
    public function action()
    {
        $uid = $this->getRequest('uid');
        if (empty($uid) ){
            header('Location:/privilege/userlist.php');
        }
        $pObj = new ManagePrivilege();
        $ret = $pObj->deleteUser($uid);
        header('Location:/privilege/userlist.php');
        exit;
    }
}
new addPrivilege();
?>