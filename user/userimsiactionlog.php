<?php
include_once '../controller.php';

class userimsiactionlog extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $searchCondition = $this->getRequest('searchCondition', 'uimid');
        $searchContent = $this->getRequest('searchContent', '');
        if (empty($currentPage)) {
            $currentPage = 0;
        } 
        if (empty($perPage)) {
            $perPage = 20;
        }
        
        $pageBanner = "";
        $baseUri = "/user/userimsiactionlog.php?perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        $loglist = array();
        $totalCount = 0;
        
        if (!empty($searchContent)) {
            $column = $searchCondition;
            $columnValue = $searchContent;
        } else {
            $column = $columnValue = '';
        }
        $manageobj = new ManageImsi();
        $resultList = $manageobj->getActionLogListByColumnSearch($column, $columnValue, $currentPage + 1, $perPage);
        if (!empty($resultList)) {
            $actionlogobj = new ActionLog();
            $actiontypelist = $actionlogobj->ACTION_TYPE_LIST;
            foreach ($resultList as $value) {
                $value['actiontype'] = $actiontypelist[$value['actiontype']];
                $loglist[] = $value;
            }
            $totalCount = $manageobj->getActionLogCountByColumnSearch($column, $columnValue);
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
        $smartyObj->assign('userimsiactionlogside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("user/userimsiactionlog.html"); 
    }
}
new userimsiactionlog();
?>