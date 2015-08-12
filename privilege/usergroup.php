<?php
include_once '../controller.php';

class addPrivilege extends controller
{
    public function action()
    {
        $uid = $this->getRequest('uid');
        $pObj = new ManagePrivilege();
        $userinfo = current($pObj->getUser($uid));
        $userGroup = $pObj->getUserGroup($uid);
        $groups = $pObj->getGroups();
        foreach ($groups as $key=>$group){
            $gid = $group['id'];
            $groups[$key]['member'] = in_array($gid, $userGroup);
        }
        $smartyobj = $this->getSmartyObj();
        $smartyobj->assign('groups', $groups);
        $smartyobj->assign('userinfo', $userinfo);
        $smartyobj->display("privilege/usergroup.html");
    }
}
new addPrivilege();
?>