<?php
include_once '../controller.php';

class index extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage  = $this->getRequest('perPage', 20) + 0;
        $albumid  = (int)$this->getRequest('albumid', 0);
        $tagid    = (int)$this->getRequest('tagid', 0);
        $status   = (int)$this->getRequest('status', 1);
        $keywords = $this->getRequest('keywords', '');

        $search_filter = $where = array();

        if (empty($currentPage)) {
            $currentPage = 0;
        } 
        if (empty($perPage)) {
            $perPage = 20;
        }

        $where['status']  =  $status;

        if ($albumid) {
            $search_filter['albumid'] = $albumid;
            $where[] = " `albumid` = '{$albumid}' ";
        }

        if ($keywords) {
            $search_filter['keywords'] = $keywords;
            $where[] = " `content` like '%{$keywords}%' ";
        }
        
        $pageBanner = "";
        $baseUri = "/comment/taglist.php?perPage={$perPage}&";
        
    	
        $ssoList = array();
        $ssoObj = new Sso();
        
        $manageTagObj = new ManageTag();
        if ($where) {
            $where = implode(" AND ", $where);
        }
        $tagList = $manageTagObj->getTagList($where, $currentPage + 1, $perPage);

        $totalCount = $manageTagObj->getTagTotalCount($where);
        
        if ($totalCount > $perPage) {
            $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('search_filter', $search_filter);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('tagList', $tagList);
        $smartyObj->assign('tagactive', "active");
        $smartyObj->display("comment/tag_list.html"); 
    }
}
new index();
?>