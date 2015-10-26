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
        
        $ssoList = array();
        $ssoObj = new Sso();
        if (!empty($searchContent)) {
            // 搜索
            if ($searchCondition == 'uid') {
                $uid = intval($searchContent);
                $ssoInfo = $ssoObj->getInfoWithUid($uid);
                if (empty($ssoInfo)) {
                    $this->showErrorJson(ErrorConf::userNoExist());
                }
                $userObj = new User();
                $userList = $userObj->getUserInfo($uid);
            }
            if(empty($userList)) {
                $this->showErrorJson(ErrorConf::userNoExist());
            }
            $ssoList = array($ssoInfo['uid'] => $ssoInfo);
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
                $ssoList = $ssoObj->getInfoWithUids($uids);
            }
            
            if ($totalCount > $perPage) {
                $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
            }
        }
        
        $aliOssObj = new AliOss();
        $dealUserList = array();
        foreach ($userList as $value) {
            $value['avatar'] = $aliOssObj->getAvatarUrl($value['uid'], $value['avatartime'], 100);
            //$value['phone'] = substr($ssoList[$value['uid']]['phonenumber'], 2);
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
        $smartyObj->display("user/getuserlist.html"); 
    }
}
new getuserlist();
?>