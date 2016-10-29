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
        $dealUserList = array();

        if (!empty($searchContent)) {
            $column = $searchCondition;
            $columnValue = $searchContent;
        } else {
            $column = $columnValue = '';
        }
        $manageobj = new ManageUser();
        $status = "";

        $db = "share_main";
        $table = "user_info";
        $userList = $manageobj->getListByColumnSearch($column, $columnValue, $status, $currentPage + 1, $perPage, $db, $table);

        if (!empty($userList)) {
            $totalCount = $manageobj->getCountByColumnSearch($column, $columnValue, $status, $db, $table);
            if ($totalCount > $perPage) {
                $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
            }

            $uids = array();
            foreach ($userList as $userValue) {
                $uids[] = $userValue['uid'];
            }
            if (!empty($uids)) {
                $userimsilist = $manageimsiobj->getUserImsiListByUids($uids);
            }

            $configvarobj = new ConfigVar();
            $statusnamelist = $configvarobj->OPTION_STATUS_NAME;

            $aliOssObj = new AliOss();

            foreach ($userList as $value) {
                $value['avatar'] = $aliOssObj->getAvatarUrl($value['uid'], $value['avatartime'], 80);
                $value['statusname'] = $statusnamelist[$value['status']];
                $value['uimid'] = $userimsilist[$value['uid']]['uimid'];
                $dealUserList[] = $value;
            }
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