<?php
include_once '../controller.php';

class getuserlist extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $searchCondition = $this->getRequest('searchCondition', 'uid');
        $searchContent = $this->getRequest('searchContent', '');
        if (empty($currentPage)) {
            $currentPage = 0;
        } 
        if (empty($perPage)) {
            $perPage = 20;
        }
        
        $pageBanner = "";
        $baseUri = "/user/getuserlist.php?perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        
        $userList = array();
        $userObj = new User();
        $manageimsiobj = new ManageImsi();
        $totalCount = 1;
        if (!empty($searchContent)) {
            // 搜索
            if ($searchCondition == 'uid') {
                $uid = intval($searchContent);
            } elseif ($searchCondition == 'nickname') {
                $nicknamemd5obj = new NicknameMd5();
                $uid = $nicknamemd5obj->checkNameIsExist($searchContent);
            }
            
            $userList = $userObj->getUserInfo($uid);
            if(empty($userList)) {
                $this->showErrorJson(ErrorConf::userNoExist());
            }
            $userimsilist = $manageimsiobj->getUserImsiListByUids($uid);
        } else {
            $manageUserObj = new ManageUser();
            $userList = $manageUserObj->getUserList($currentPage + 1, $perPage);
            if(empty($userList)) {
                $this->showErrorJson(ErrorConf::userNoExist());
            }
            $totalCount = $manageUserObj->getUserTotalCount();
           
            $uids = array();
            foreach ($userList as $userValue) {
                $uids[] = $userValue['uid'];
            }
            if (!empty($uids)) {
                $userimsilist = $manageimsiobj->getUserImsiListByUids($uids);
            }
            
            if ($totalCount > $perPage) {
                $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
            }
        }
        
        $configvarobj = new ConfigVar();
        $statusnamelist = $configvarobj->OPTION_STATUS_NAME;
        
        $aliOssObj = new AliOss();
        $dealUserList = array();
        foreach ($userList as $value) {
            $value['avatar'] = $aliOssObj->getAvatarUrl($value['uid'], $value['avatartime'], 80);
            $value['statusname'] = $statusnamelist[$value['status']];
            $value['uimid'] = $userimsilist[$value['uid']]['uimid'];
            $dealUserList[] = $value;
        }
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('userList', $dealUserList);
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('searchCondition', $searchCondition);
        $smartyObj->assign('searchContent', $searchContent);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('useractive', "active");
        $smartyObj->assign('getuserlistside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("user/getuserlist.html"); 
    }
}
new getuserlist();
?>