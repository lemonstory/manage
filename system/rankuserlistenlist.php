<?php
include_once '../controller.php';

class rankuserlistenlist extends controller
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
        $baseUri = "/system/rankuserlistenlist.php?perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        $totalCount = 0;
        
    	$newonlinelist = array();
    	if (!empty($searchContent)) {
    	    $uid = $searchContent;
        } else {
            $uid = 0;
        }
        
        $rankuserlistenlist = array();
        $managelistenobj = new ManageListen();
        $resultList = $managelistenobj->getRankUserListenListBySearch($uid, $currentPage + 1, $perPage);
        if (!empty($resultList)) {
            $uids = array();
            foreach ($resultList as $uidkey => $num) {
                $uids[] = $uidkey;
            }
            if (!empty($uids)) {
                $uids = array_unique($uids);
                $userobj = new User();
                $userreslist = $userobj->getUserInfo($uids);
                $aliossobj = new AliOss();
                $configvarobj = new ConfigVar();
                foreach ($userreslist as $value) {
                    $value['num'] = $resultList[$value['uid']];
                    if (!empty($value['avatartime'])) {
                        $value['avatar'] = $aliossobj->getAvatarUrl($value['uid'], $value['avatartime'], 80);
                    }
                    $value['status'] = $configvarobj->OPTION_STATUS_NAME[$value['status']];
                    $rankuserlistenlist[] = $value;
                }
            }
            
            $totalCount = $managelistenobj->getRankUserListenCountBySearch($uid);
            if ($totalCount > $perPage) {
                $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
            }
        }
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('searchCondition', $searchCondition);
        $smartyObj->assign('searchContent', $searchContent);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('rankuserlistenlist', $rankuserlistenlist);
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('rankuserlistenside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("system/rankuserlistenlist.html"); 
    }
}
new rankuserlistenlist();
?>