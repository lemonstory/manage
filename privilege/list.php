<?php
include_once '../controller.php';

class listPrivilege extends controller
{
    public function action()
    {
        $pObj = new ManagePrivilege();
        $privileges = $pObj->getPrivileges();
        $smartyobj = $this->getSmartyObj();
        $smartyobj->assign('privileges', $privileges);
        $smartyobj->assign("privilege", "active");
        $smartyobj->assign("listside", "active");
        $smartyobj->display("privilege/list.html");
    }
}
new listPrivilege();
?>