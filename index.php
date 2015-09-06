<?php
include_once './controller.php';

class index extends controller
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
        $uid = $this->getUid();
        if (empty($uid)) {
            $this->redirect('/user/login.php');
        }
        
        $smartyobj = $this->getSmartyObj();
        $smartyobj->assign("indexactive", "active");
        $smartyobj->assign("indexside", "active");
        $smartyobj->display("index.html");
    }
}
new index();
?>