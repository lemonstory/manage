<?php
include_once '../controller.php';

class loginsave extends controller
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
        $userName = $this->getRequest('username');
        $password = $this->getRequest('password');
        if (empty($userName) || empty($password)) {
            $this->showErrorJson("参数错误");
        }
        if(strlen($userName) != 11 || $userName + 0 == 0 || substr($userName, 0, 1) != 1)
        {
            $this->showErrorJson(ErrorConf::phoneNumberIsIllegal());
        }
        
        $SsoObj = new Sso();
        $usrinfo = $SsoObj->phoneLogin($userName, $password);
        if($usrinfo === false) {
            $this->showErrorJson($SsoObj->getError());
        }
        
        $this->showSuccJson($usrinfo);
    }
}
new loginsave();