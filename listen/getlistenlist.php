<?php
include_once '../controller.php';

class getlistenlist extends controller
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
        $baseUri = "/listen/getlistenlist.php?perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        $listenlist = array();
        $totalCount = 0;
        
        if (!empty($searchContent)) {
            if ($searchCondition == 'uid') {
                $uimobj = new UserImsi();
                $uimid = $uimobj->getUimid($searchContent);
                $column = $searchCondition = "uimid";
                $columnValue = $searchContent = $uimid;
            } else {
                $column = $searchCondition;
                $columnValue = $searchContent;
            }
        } else {
            $column = $columnValue = '';
        }
        $manageobj = new ManageListen();
        $resultList = $manageobj->getListByColumnSearch($column, $columnValue, $currentPage + 1, $perPage);
        if (!empty($resultList)) {
            $albumids = array();
            $storyids = array();
            $albumlist = array();
            foreach ($resultList as $value) {
                $albumids[] = $value['albumid'];
                $storyids[] = $value['storyid'];
            }
            
            if (!empty($albumids)) {
                $albumids = array_unique($albumids);
                $albumobj = new Album();
                $albumlist = $albumobj->getListByIds($albumids);
            }
            if (!empty($storyids)) {
                $storyids = array_unique($storyids);
                $storyobj = new Story();
                $storylist = $storyobj->getListByIds($storyids);
            }
            
            $aliossobj = new AliOss();
            foreach ($resultList as $value) {
                $albumid = $value['albumid'];
                $storyid = $value['storyid'];
                if (empty($albumlist[$albumid])) {
                    continue;
                }
                $albuminfo = $albumlist[$albumid];
                if (!empty($albuminfo['cover'])) {
                    $albuminfo['cover'] = $aliossobj->getImageUrlNg($albuminfo['cover'], 100);
                }
                $value['albuminfo'] = $albuminfo;
                
                $storyinfo = $storylist[$storyid];
                $value['storyinfo'] = $storyinfo;
                $value['uptime'] = date("Y-m-d H:i:s", $value['uptime']);
                $listenlist[] = $value;
            }
            
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
        $smartyObj->assign('listenlist', $listenlist);
        $smartyObj->assign('useractive', "active");
        $smartyObj->assign('getlistenlistside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("listen/getlistenlist.html"); 
    }
}
new getlistenlist();
?>