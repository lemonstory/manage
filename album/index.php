<?php
include_once '../controller.php';

class index extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $where = array();
        
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
            $where[] = "`title` like '%{$title}%' ";
        }
        if ($albumid) {
            $search_filter['albumid'] = $albumid;
            $where[] = "`id` ='{$albumid}' ";
        }

        $pageBanner = "";
        $baseUri = "/album/index.php?albumid={$albumid}&title={$title}";
        
        $manageAlbumObj = new ManageAlbum();
        // where 处理
        if ($where) {
            $where = implode(" AND ", $where);
        }
        $albumList = $manageAlbumObj->getAlbumList($where, $currentPage + 1, $perPage);
        $totalCount = $manageAlbumObj->getAlbumTotalCount($where);
        
        if ($totalCount > $perPage) {
            $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('search_filter', $search_filter);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('albumList', $albumList);
        $smartyObj->assign('albumactive', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("album/index.html"); 
    }
}
new index();
?>