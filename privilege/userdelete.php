<?php
include_once '../controller.php';

class userdelete extends controller
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
new userdelete();
?>