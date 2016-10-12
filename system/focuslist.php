<?php
include_once '../controller.php';

class focuslist extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $status = $this->getRequest('status', 0);
        $searchCondition = $this->getRequest('searchCondition', 'id');
        $searchContent = $this->getRequest('searchContent', '');
        if (empty($currentPage)) {
            $currentPage = 0;
        } 
        if (empty($perPage)) {
            $perPage = 20;
        }
        
        $pageBanner = "";
        $baseUri = "/system/focuslist.php?perPage={$perPage}&status={$status}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        
    	$focuslist = array();
    	if (!empty($searchContent)) {
    	    $column = $searchCondition;
            $columnValue = $searchContent;
        } else {
            $column = $columnValue = '';
        }
        $managesysobj = new ManageSystem();
        $manageFocusCategoryObj = new ManageFocusCategory();
        $categoryList = $manageFocusCategoryObj->getList();
        $resultList = $managesysobj->getRecommendListByColumnSearch("share_manage", "focus", $column, $columnValue, $status, $currentPage + 1, $perPage);
        if (!empty($resultList)) {
            $aliossobj = new AliOss();
            foreach ($resultList as $value) {
                $value['cover'] = $aliossobj->getFocusUrl($value['id'], $value['covertime'], 1);
                $focuslist[] = $value;
            }
            
            $totalCount = $managesysobj->getRecommendCountByColumnSearch("share_manage", "focus", $column, $columnValue, $status);
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
        $smartyObj->assign('focuslist', $focuslist);
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('focusside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->assign("categoryList", $categoryList);
        $smartyObj->display("system/focuslist.html");
    }
}
new focuslist();
?>