<?php
include_once '../controller.php';

class focuslist extends controller
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
        $baseUri = "/user/focuslist.php?perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        
    	$focuslist = array();
    	if (!empty($searchContent)) {
            $columnValue = $searchContent;
        } else {
            $column = $columnValue = '';
        }
        $managesysobj = new ManageSystem();
        $resultList = $managesysobj->getRecommendListByColumnSearch("share_manage", "focus", $column, $columnValue, $currentPage + 1, $perPage);
        if (!empty($resultList)) {
            $aliossobj = new AliOss();
            foreach ($resultList as $value) {
                $value['cover'] = $aliossobj->getFocusUrl($value['picid']);
                $focuslist[] = $value;
            }
            
            $totalCount = $managesysobj->getRecommendCountByColumnSearch("share_manage", "focus", $column, $columnValue);
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
        $smartyobj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("system/focuslist.html"); 
    }
}
new focuslist();
?>