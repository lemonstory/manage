<?php
include_once '../controller.php';

class userleavegroup extends controller
{
    public function action()
    {
        $uid = $this->getRequest('uid');
        $groupid = $this->getRequest('groupid');
        if (empty($uid)){
            die('uid empty');
        }
        if (empty($groupid)){
            die('groupid empty');
        }
        
        $pObj = new ManagePrivilege();
        $ret = $pObj->userLeaveGroup($uid, $groupid);
        header('Location:/privilege/usergroup.php?uid='.$uid);
    }
}
new userleavegroup();
?>