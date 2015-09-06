<?php
include_once '../controller.php';

class index extends controller
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
        $baseUri = "/comment/index.php?perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        
    	
        $ssoList = array();
        $ssoObj = new Sso();
        if (!empty($searchContent)) {
            // 搜索
            // if ($searchCondition == 'uid') {
            //     $uid = intval($searchContent);
            //     $ssoInfo = $ssoObj->getInfoWithUid($uid);
            //     if (empty($ssoInfo)) {
            //         $this->showErrorJson("账户不存在");
            //     }
            //     $commentObj = new Comment();
            //     $commentList = $commentObj->getCommentInfo($uid);
            // } elseif ($searchCondition == 'phone') {
            //     $phoneNumber = $searchContent;
            //     $ssoInfo = $ssoObj->getInfoWithPhoneNumber($phoneNumber);
            //     if (empty($ssoInfo)) {
            //         $this->showErrorJson("账户不存在");
            //     }
            //     $uid = $ssoInfo['uid'];
            //     $commentObj = new Comment();
            //     $commentList = $commentObj->getCommentInfo($uid);
            // }
            // if(empty($commentList)) {
            //     $this->showErrorJson("用户数据为空");
            // }
            // $ssoList = array($ssoInfo['uid'] => $ssoInfo);
        } else {
            $manageCommentObj = new ManageComment();
            $commentList = $manageCommentObj->getCommentList($currentPage + 1, $perPage);
            if(empty($commentList)) {
                $this->showErrorJson("专辑数据为空");
            }
            $totalCount = $manageCommentObj->getCommentTotalCount();
            
            if ($totalCount > $perPage) {
                $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
            }
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        // $smartyObj->assign('searchCondition', $searchCondition);
        // $smartyObj->assign('searchContent', $searchContent);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('commentList', $commentList);
        $smartyObj->assign('commentactive', "active");
        $smartyObj->display("comment/comment_list.html"); 
    }
}
new index();
?>