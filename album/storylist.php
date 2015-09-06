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
        $baseUri = "/story/index.php?perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        
    	
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
            //     $storyObj = new Story();
            //     $storyList = $storyObj->getStoryInfo($uid);
            // } elseif ($searchCondition == 'phone') {
            //     $phoneNumber = $searchContent;
            //     $ssoInfo = $ssoObj->getInfoWithPhoneNumber($phoneNumber);
            //     if (empty($ssoInfo)) {
            //         $this->showErrorJson("账户不存在");
            //     }
            //     $uid = $ssoInfo['uid'];
            //     $storyObj = new Story();
            //     $storyList = $storyObj->getStoryInfo($uid);
            // }
            // if(empty($storyList)) {
            //     $this->showErrorJson("用户数据为空");
            // }
            // $ssoList = array($ssoInfo['uid'] => $ssoInfo);
        } else {
            $manageStoryObj = new ManageStory();
            $storyList = $manageStoryObj->getStoryList($currentPage + 1, $perPage);
            if(empty($storyList)) {
                $this->showErrorJson("专辑数据为空");
            }
            $totalCount = $manageStoryObj->getStoryTotalCount();
            
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
        $smartyObj->assign('storyList', $storyList);
        $smartyObj->assign('albumactive', "active");
        $smartyObj->display("album/story_list.html"); 
    }
}
new index();
?>