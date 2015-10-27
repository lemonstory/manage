<?php
include_once '../controller.php';

class index extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage     = $this->getRequest('perPage', 20) + 0;
        $content     = $this->getRequest('content', '');
        $albumid     = (int)$this->getRequest('albumid', 0);
        $status      = (int)$this->getRequest('status', 1);

        $searchFilter = $where = array();
        $where[] = "`status` = {$status} ";

        if (empty($currentPage)) {
            $currentPage = 0;
        } 
        if (empty($perPage)) {
            $perPage = 20;
        }

        if ($albumid) {
            $searchFilter['albumid'] = $albumid;
            $where[] = "`albumid` = {$albumid} ";
        }
        if ($content) {
            $searchFilter['content'] = $content;
            $where[] = "`content` like '%{$content}%'";
        }

        if ($where) {
            $where = implode(" AND ", $where);
        }

        $pageBanner = "";
        $baseUri = "/comment/commentlist.php?perPage={$perPage}";

        $manageCommentObj = new ManageComment();
        $commentList = $manageCommentObj->getCommentList($where, $currentPage + 1, $perPage);
        if(empty($commentList)) {
            $this->showErrorJson("专辑数据为空");
        }
        $totalCount = $manageCommentObj->getCommentTotalCount($where);
        
        if ($totalCount > $perPage) {
            $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('commentList', $commentList);
        $smartyObj->assign('searchFilter', $searchFilter);
        $smartyObj->assign('commentlistactive', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("comment/comment_list.html"); 
    }
}
new index();
?>