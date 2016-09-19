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
        $user = new User();
        $uid = $ssoobj->userReg($username, $username, $password,$user->TYPE_PH,$user->IDENTITY_SYSTEM_ADMIN);

        if($uid) {
            $pObj = new ManagePrivilege();
            $ret = $pObj->addUser($uid, $name);
            header('Location:/privilege/userlist.php');
            exit;
        }else{
            echo "注册用户失败";
        }
    }
}
new useradd();
?>