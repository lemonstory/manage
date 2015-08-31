<?php
include_once '../controller.php';

class listPrivilege extends controller
{
	// @huqq delete
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
        $pObj = new ManagePrivilege();
        $privileges = $pObj->getPrivileges();
        $smartyobj = $this->getSmartyObj();
        $smartyobj->assign('privileges', $privileges);
        $smartyobj->display("privilege/list.html");
    }
}
new listPrivilege();
?>