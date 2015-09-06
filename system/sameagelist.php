<?php
include_once '../controller.php';

class sameagelist extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $searchCondition = $this->getRequest('searchCondition', 'babyagetype');
        $searchContent = $this->getRequest('searchContent', '');
        if (empty($currentPage)) {
            $currentPage = 0;
        } 
        if (empty($perPage)) {
            $perPage = 20;
        }
        
        $pageBanner = "";
        $baseUri = "/user/sameagelist.php?perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        $sameagelist = array();
    	if (!empty($searchContent)) {
            $columnValue = $searchContent;
        } else {
            $column = $columnValue = '';
        }
        $managelistenobj = new ManageListen();
        $resultList = $managelistenobj->getSameAgeListByColumnSearch($column, $columnValue, $currentPage + 1, $perPage);
        if (!empty($resultList)) {
            foreach ($resultList as $value) {
                $sameagelist[] = $value;
            }
            $totalCount = $managelistenobj->getSameAgeCountByColumnSearch($column, $columnValue);
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
        $smartyObj->assign('sameagelist', $sameagelist);
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('sameageside', "active");
        $smartyObj->display("system/sameagelist.html"); 
    }
}
new sameagelist();
?>