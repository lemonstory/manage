<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 2016/11/17
 * Time: 上午10:10
 */

include_once '../controller.php';

class ddInfoList extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage     = $this->getRequest('perPage', 20) + 0;
        $title     = $this->getRequest('title', '');
        $author     = $this->getRequest('author', '');
        $baseUri = "/comment/ddInfoList.php?perPage={$perPage}";

        $searchFilter = $where = array();
        if (empty($currentPage)) {
            $currentPage = 0;
        }
        if (empty($perPage)) {
            $perPage = 20;
        }
        if(!empty($title)){
            $where['title'] = $title;
            $searchFilter['title'] = $title;
        }
        if(!empty($author)){
            $where['author'] = $author;
            $searchFilter['author'] = $author;
        }
        $manageCollectionDdLog = new ManageCollectionDdLog();
        
        $totalCount = $manageCollectionDdLog->getDdInfoTotalCount($where);
        $ddList = $manageCollectionDdLog->getDdInfoList($where,$currentPage + 1, $perPage);
        
        $pageBanner = "";
        if ($totalCount > $perPage) {
            $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
        }
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('ddList', $ddList);
        $smartyObj->assign('searchFilter', $searchFilter);
        $smartyObj->assign('ddinfolistactive', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("comment/ddInfoList.html");
    }
}
new ddInfoList();