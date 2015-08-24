<?php
include_once '../controller.php';

class logout extends controller
{
    public function filters()
    {
        return array(
            'authLogin' => array(
                'requireLogin' => false,
            ),
            'privilege' => array(
                'checkPrivilege' => false,
            ),
        );
    }
    
    public function action()
    {
        $SsoObj = new Sso();
        $SsoObj->logout();
        $this->redirect("/user/login.php");
    }
}
new logout();