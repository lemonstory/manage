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
        $title     = $this->getRequest('title', '');
        $author     = $this->getRequest('author', '');

        $searchFilter = $where = array();
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
        $ddList = $manageCollectionDdLog->getDdInfoList($where);
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('ddList', $ddList);
        $smartyObj->assign('searchFilter', $searchFilter);
        $smartyObj->assign('ddinfolistactive', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("comment/ddInfoList.html");
    }
}
new ddInfoList();