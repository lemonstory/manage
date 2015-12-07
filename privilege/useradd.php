<?php
include_once '../controller.php';

class useradd extends controller
{
    public function action()
    {
        $username = $this->getRequest('username');
        $password = $this->getRequest('password');
        $name = $this->getRequest('name');
        if (empty($username) || empty($password) || empty($name)){
            header('Location:/privilege/userlist.php');
        }
        
        $ssoobj = new Sso();
        $uid = $ssoobj->phonereg($username, $username, $password);
        
        $pObj = new ManagePrivilege();
        $ret = $pObj->addUser($uid, $name);
        header('Location:/privilege/userlist.php');
        exit;
    }
}
new useradd();
?>