<?php
include_once '../controller.php';

class useradd extends controller
{
    public function action()
    {
        $uid = $this->getRequest('uid');
        $name = $this->getRequest('name');
        if (empty($uid) || empty($name)){
            header('Location:/privilege/userlist.php');
        }
        $pObj = new ManagePrivilege();
        $ret = $pObj->addUser($uid, $name);
        header('Location:/privilege/userlist.php');
        exit;
    }
}
new useradd();
?>