<?php
include_once '../controller.php';

class grouplist extends controller
{
    public function action()
    {
        $pObj = new ManagePrivilege();
        $groups = $pObj->getGroups('');
        $smartyobj = $this->getSmartyObj();
        $smartyobj->assign('groups', $groups);
        $smartyobj->assign("privilege", "active");
        $smartyobj->assign("grouplistside", "active");
        $smartyobj->display("privilege/grouplist.html");
    }
}
new grouplist();
?>