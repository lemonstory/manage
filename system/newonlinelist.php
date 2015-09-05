<?php
include_once '../controller.php';

class newonlinelist extends controller
{
	// @huqq delete
	public function filters()
    {
        return array(
            'authLogin' => array(
                'requireLogin' => false,
            ),
            'privilege' => array(
                'checkPrivilege' => false,
            ),
        );
    }
    
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
        $baseUri = "/user/newonlinelist.php?perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        
    	$newonlinelist = array();
    	if (!empty($searchContent)) {
            $columnValue = $searchContent;
        } else {
            $column = $columnValue = '';
        }
        $managesysobj = new ManageSystem();
        $resultList = $managesysobj->getRecommendListByColumnSearch("share_main", "recommend_new_online", $column, $columnValue, $currentPage + 1, $perPage);
        if (!empty($resultList)) {
            foreach ($resultList as $value) {
                $newonlinelist[] = $value;
            }
            $totalCount = $managesysobj->getRecommendCountByColumnSearch("share_main", "recommend_new_online", $column, $columnValue);
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
        $smartyObj->assign('newonlinelist', $newonlinelist);
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('newonlineside', "active");
        $smartyObj->display("system/newonlinelist.html"); 
    }
}
new newonlinelist();
?>