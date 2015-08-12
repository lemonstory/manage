<?php
include_once '../controller.php';

class addPrivilege extends controller
{
    public function action()
    {
        $pObj = new ManagePrivilege();
        $privileges = $pObj->getPrivileges();
        $smartyobj = $this->getSmartyObj();
        $smartyobj->assign('privileges', $privileges);
        $smartyobj->display("privilege/list.html");
    }
}
new addPrivilege();
?>