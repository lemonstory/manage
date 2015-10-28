<?php
include_once '../controller.php';

class userloginlog extends controller
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
        $baseUri = "/user/userloginlog.php?perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        $loglist = array();
        $totalCount = 0;
        
        if (!empty($searchContent)) {
            $column = $searchCondition;
            $columnValue = $searchContent;
        } else {
            $column = $columnValue = '';
        }
        $manageobj = new ManageLog();
        $loglist = $manageobj->getListByColumnSearch($column, $columnValue, $currentPage + 1, $perPage);
        if (!empty($loglist)) {
            $totalCount = $manageobj->getCountByColumnSearch($column, $columnValue);
            if ($totalCount > $perPage) {
                $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
            }
        }
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('searchCondition', $searchCondition);
        $smartyObj->assign('searchContent', $searchContent);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('loglist', $loglist);
        $smartyObj->assign('useractive', "active");
        $smartyObj->assign('userloginlogside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("user/userloginlog.html"); 
    }
}
new userloginlog();
?>