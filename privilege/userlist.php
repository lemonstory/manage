<?php
include_once '../controller.php';

class userlist extends controller
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
        $page = $this->getRequest('p');
        $perpage = $this->getRequest('perPage',20);
        $pObj = new ManagePrivilege();
        //$userlist = $pObj->getUserList();
        $smartyobj = $this->getSmartyObj();
        //$smartyobj->assign('userlist', $userlist);
        $smartyobj->assign('priactive', "active");
        $smartyobj->display("privilege/userlist.html");
    }
}
new userlist();
?>