<?php
include_once '../controller.php';

class sameagelist extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $status = $this->getRequest('status', 0);
        $searchCondition = $this->getRequest('searchCondition', 'babyagetype');
        $searchContent = $this->getRequest('searchContent', '');
        $selectContent = $this->getRequest('selectContent', 0);
        if (empty($currentPage)) {
            $currentPage = 0;
        } 
        if (empty($perPage)) {
            $perPage = 20;
        }
        
        $pageBanner = "";
        $baseUri = "/system/sameagelist.php?perPage={$perPage}&status={$status}&searchCondition={$searchCondition}&searchContent={$searchContent}&selectContent={$selectContent}";
        $sameagelist = array();
        $totalCount = 0;
        
        if (!empty($searchContent) && empty($selectContent)) {
            $column = $searchCondition;
            $columnValue = $searchContent;
        } elseif (!empty($selectContent) && empty($searchContent)) {
            $column = $searchCondition;
            $columnValue = $selectContent;
        } else {
            $column = $columnValue = '';
        }
        $configvarobj = new ConfigVar();
        $agetypenamelist = $configvarobj->AGE_TYPE_NAME_LIST;
        
        $managelistenobj = new ManageListen();
        $resultList = $managelistenobj->getSameAgeListByColumnSearch($column, $columnValue, $status, $currentPage + 1, $perPage);
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
                    $albuminfo['cover'] = $aliossobj->getImageUrlNg($albuminfo['cover'], 100);
                }
                $value['albuminfo'] = $albuminfo;
                $value['agetypename'] = $agetypenamelist[$value['agetype']];
                $sameagelist[] = $value;
            }
            $totalCount = $managelistenobj->getSameAgeCountByColumnSearch($column, $columnValue, $status);
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
        $smartyObj->assign('selectContent', $selectContent);
        $smartyObj->assign('status', $status);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign("agetypenamelist", $agetypenamelist);
        $smartyObj->assign('sameagelist', $sameagelist);
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('sameageside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("system/sameagelist.html"); 
    }
}
new sameagelist();
?>