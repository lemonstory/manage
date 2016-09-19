<?php
include_once '../controller.php';

class userlist extends controller
{
    public function action()
    {
        $page = $this->getRequest('p');
        $perpage = $this->getRequest('perPage', 20);
        $searchCondition = $this->getRequest('searchCondition', 'uid');
        $searchContent = $this->getRequest('searchContent', '');
        if (empty($currentPage)) {
            $currentPage = 0;
        }
        if (empty($perPage)) {
            $perPage = 20;
        }
        
        $pageBanner = "";
        $baseUri = "/privilege/userlist.php?perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        
        $pObj = new ManagePrivilege();
        $reslist = $pObj->getUserList();
        
        $adminlist = array();
        if (!empty($reslist)) {
            $uids = array();
            foreach($reslist as $value) {
                $uids[] = $value['uid'];
            }
            
            $userObj = new User();
            $userlist = $userObj->getUserInfo($uids);
            
            foreach($reslist as $value) {
                $userinfo = $userlist[$value['uid']];
                $userinfo['name'] = $value['name'];
                $adminlist[] = $userinfo;
            }
        }
        
        
        $smartyobj = $this->getSmartyObj();
        $smartyobj->assign('adminlist', $adminlist);
        $smartyobj->assign('privilege', "active");
        $smartyobj->assign('userlistside', "active");
        $smartyobj->assign("headerdata", $this->headerCommonData());
        $smartyobj->display("privilege/userlist.html");
    }
}
new userlist();
?>