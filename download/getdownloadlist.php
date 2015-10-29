<?php
include_once '../controller.php';

class getdownloadlist extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $status = $this->getRequest('status', 0);
        $searchCondition = $this->getRequest('searchCondition', 'uimid');
        $searchContent = $this->getRequest('searchContent', '');
        if (empty($currentPage)) {
            $currentPage = 0;
        } 
        if (empty($perPage)) {
            $perPage = 20;
        }
        
        $pageBanner = "";
        $baseUri = "/download/getdownloadlist.php?perPage={$perPage}&status={$status}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        $downloadlist = array();
        $totalCount = 0;
        
        if (!empty($searchContent)) {
            $column = $searchCondition;
            $columnValue = $searchContent;
        } else {
            $column = $columnValue = '';
        }
        $manageobj = new ManageDownLoad();
        $resultList = $manageobj->getListByColumnSearch($column, $columnValue, $status, $currentPage + 1, $perPage);
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
                $downloadlist[] = $value;
            }
            
            $totalCount = $manageobj->getCountByColumnSearch($column, $columnValue, $status);
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
        $smartyObj->assign('downloadlist', $downloadlist);
        $smartyObj->assign('useractive', "active");
        $smartyObj->assign('getdownloadlistside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("download/getdownloadlist.html"); 
    }
}
new getdownloadlist();
?>