<?php
include_once '../controller.php';

class userlist extends controller
{
    public function action()
    {
        $page = $this->getRequest('p');
        $perpage = $this->getRequest('perPage',20);
        $pObj = new ManagePrivilege();
        //$userlist = $pObj->getUserList();
        $smartyobj = $this->getSmartyObj();
        //$smartyobj->assign('userlist', $userlist);
        $smartyobj->assign('privilege', "active");
        $smartyobj->assign('userlistside', "active");
        $smartyobj->assign("headerdata", $this->headerCommonData());
        $smartyobj->display("privilege/userlist.html");
    }
}
new userlist();
?>