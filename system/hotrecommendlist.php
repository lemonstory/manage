<?php
include_once '../controller.php';

class hotrecommendlist extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $status = $this->getRequest('status', 0);
        $searchCondition = $this->getRequest('searchCondition', 'uid');
        $searchContent = $this->getRequest('searchContent', '');
        if (empty($currentPage)) {
            $currentPage = 0;
        }
        if (empty($perPage)) {
            $perPage = 20;
        }
        
        $pageBanner = "";
        $baseUri = "/system/hotrecommendlist.php?perPage={$perPage}&status={$status}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        $totalCount = 0;
    	$hotlist = array();
    	if (!empty($searchContent)) {
    	    $column = $searchCondition;
            $columnValue = $searchContent;
        } else {
            $column = $columnValue = '';
        }
        $managesysobj = new ManageSystem();
        $resultList = $managesysobj->getRecommendListByColumnSearch("share_main", "recommend_hot", $column, $columnValue, $status, $currentPage + 1, $perPage);
        if (!empty($resultList)) {
            $albumids = array();
            $albumlist = array();
            foreach ($resultList as $value) {
                $albumids[] = $value['albumid'];
            }
            if (!empty($albumids)) {
                $albumids = array_unique($albumids);
                $albumobj = new Album();
                $albumlist = $albumobj->getListByIds($albumids);
            }
            
            $aliossobj = new AliOss();
            foreach ($resultList as $value) {
                $albumid = $value['albumid'];
                if (empty($albumlist[$albumid])) {
                    continue;
                }
                $albuminfo = $albumlist[$albumid];
                if (!empty($albuminfo['cover'])) {
                    $albuminfo['cover'] = $aliossobj->getImageUrlNg($albuminfo['cover'], 100, $albuminfo['cover_time']);
                }
                $value['albuminfo'] = $albuminfo;
                $hotlist[] = $value;
            }
            
            $totalCount = $managesysobj->getRecommendCountByColumnSearch("share_main", "recommend_hot", $column, $columnValue, $status);
            if ($totalCount > $perPage) {
                $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
            }
        }
        
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('searchCondition', $searchCondition);
        $smartyObj->assign('searchContent', $searchContent);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('hotlist', $hotlist);
		$smartyObj->assign('status', $status);
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('hotrecommendside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("system/hotrecommendlist.html"); 
    }
}
new hotrecommendlist();
?>