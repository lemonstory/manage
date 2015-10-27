<?php
include_once '../controller.php';

class index extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $albumid = (int)$this->getRequest('albumid', 0);
        $storyid = (int)$this->getRequest('storyid', 0);
        $title   = $this->getRequest('title', '');

        $search_filter = $where = array();

        if (empty($currentPage)) {
            $currentPage = 0;
        } 
        if (empty($perPage)) {
            $perPage = 20;
        }

        if ($albumid) {
            $search_filter['albumid'] = $albumid;
            $where[] = " `album_id` = '{$albumid}' ";
        }

        if ($storyid) {
            $search_filter['storyid'] = $storyid;
            $where[] = " `id` = '{$storyid}' ";
        }
        if ($title) {
            $search_filter['title'] = $title;
            $where[] = " `title` like '%{$title}%' ";
        }
        
        $pageBanner = "";
        $baseUri = "/story/index.php?perPage={$perPage}&";
        
    	
        $ssoList = array();
        $ssoObj = new Sso();
        
        $manageStoryObj = new ManageStory();
        if ($where) {
            $where = implode(" AND ", $where);
        }
        $storyList = $manageStoryObj->getStoryList($where, $currentPage + 1, $perPage);

        $totalCount = $manageStoryObj->getStoryTotalCount($where);
        
        if ($totalCount > $perPage) {
            $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('search_filter', $search_filter);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('storyList', $storyList);
        $smartyObj->assign('storyactive', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("album/story_list.html"); 
    }
}
new index();
?>