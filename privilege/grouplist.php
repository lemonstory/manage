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
        $smartyobj->assign("headerdata", $this->headerCommonData());
        $smartyobj->display("privilege/grouplist.html");
    }
}
new grouplist();
?>