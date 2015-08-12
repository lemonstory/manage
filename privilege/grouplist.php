<?php
include_once '../controller.php';

class addPrivilege extends controller
{
    public function action()
    {
        $pObj = new ManagePrivilege();
        $groups = $pObj->getGroups('');
        $smartyobj = $this->getSmartyObj();
        $smartyobj->assign('groups', $groups);
        $smartyobj->display("privilege/grouplist.html");
    }
}
new addPrivilege();
?>