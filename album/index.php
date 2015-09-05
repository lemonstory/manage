<?php
include_once '../controller.php';

class index extends controller
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
        $baseUri = "/album/index.php?perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        
    	
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
            //     $albumObj = new Album();
            //     $albumList = $albumObj->getAlbumInfo($uid);
            // } elseif ($searchCondition == 'phone') {
            //     $phoneNumber = $searchContent;
            //     $ssoInfo = $ssoObj->getInfoWithPhoneNumber($phoneNumber);
            //     if (empty($ssoInfo)) {
            //         $this->showErrorJson("账户不存在");
            //     }
            //     $uid = $ssoInfo['uid'];
            //     $albumObj = new Album();
            //     $albumList = $albumObj->getAlbumInfo($uid);
            // }
            // if(empty($albumList)) {
            //     $this->showErrorJson("用户数据为空");
            // }
            // $ssoList = array($ssoInfo['uid'] => $ssoInfo);
        } else {
            $manageAlbumObj = new ManageAlbum();
            $albumList = $manageAlbumObj->getAlbumList($currentPage + 1, $perPage);
            if(empty($albumList)) {
                $this->showErrorJson("专辑数据为空");
            }
            $totalCount = $manageAlbumObj->getAlbumTotalCount();
            
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
        $smartyObj->assign('albumList', $albumList);
        $smartyObj->assign('albumactive', "active");
        $smartyObj->display("album/index.html"); 
    }
}
new index();
?>