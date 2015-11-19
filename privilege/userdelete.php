<?php
include_once '../controller.php';

class userdelete extends controller
{
    public function action()
    {
        $uid = $this->getRequest('uid');
        if (empty($uid) ){
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $pObj = new ManagePrivilege();
        $ret = $pObj->deleteUser($uid);
        $this->showSuccJson();
    }
}
new userdelete();
?>