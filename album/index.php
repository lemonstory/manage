<?php
include_once '../controller.php';

class index extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        
        if (empty($currentPage)) {
            $currentPage = 0;
        } 
        if (empty($perPage)) {
            $perPage = 20;
        }

        $albumid = $this->getRequest('albumid', '');
        $title   = $this->getRequest('title', '');

        $search_filter = array();

        if ($title) {
            $search_filter['title'] = $title;
        }
        if ($albumid) {
            $search_filter['albumid'] = $albumid;
        }

        $pageBanner = "";
        $baseUri = "/album/index.php?albumid={$albumid}&title={$title}";
        
        $manageAlbumObj = new ManageAlbum();
        $albumList = $manageAlbumObj->getAlbumList($currentPage + 1, $perPage);
        if(empty($albumList)) {
            $this->showErrorJson("专辑数据为空");
        }
        $totalCount = $manageAlbumObj->getAlbumTotalCount();
        
        if ($totalCount > $perPage) {
            $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
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