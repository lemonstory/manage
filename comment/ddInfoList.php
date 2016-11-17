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
        $manageCollectionDdLog = new ManageCollectionDdLog();
        
        $totalCount = $manageCollectionDdLog->getDdInfoTotalCount();
        $ddList = $manageCollectionDdLog->getDdInfoList();
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('ddList', $ddList);
        $smartyObj->assign('ddinfolistactive', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("comment/ddInfoList.html");
    }
}
new ddInfoList();