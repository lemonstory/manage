<?php
include_once '../controller.php';

class uimidinteresttaglist extends controller
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
        $baseUri = "/interest/uimidinteresttaglist.php?perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        
        $uimidinteresttaglist = array();
        $totalCount = 0;
        
        if (!empty($searchContent)) {
            $column = $searchCondition;
            $columnValue = $searchContent;
        } else {
            $column = $columnValue = '';
        }
        $manageobj = new ManageUimidInterest();
        $resultList = $manageobj->getListByColumnSearch($column, $columnValue, $currentPage + 1, $perPage);
        if (!empty($resultList)) {
            $tagids = array();
            $uimids = array();
            $userimsilist = array();
            foreach ($resultList as $value) {
                $uimids[] = $value['uimid'];
                $tagids[] = $value['tagid'];
            }
            if (!empty($tagids)) {
                $tagnewobj = new TagNew();
                $taglist = $tagnewobj->getTagInfoByIds($tagids);
            }
            if (!empty($uimids)) {
                $manageimsiobj = new ManageImsi();
                $userimsilist = $manageimsiobj->getUserImsiListByUimids($uimids);
            }
            
            foreach ($resultList as $value) {
                $value['userimsiinfo'] = $userimsilist[$value['uimid']];
                $value['tagname'] = $taglist[$value['tagid']]['name'];
                $uimidinteresttaglist[] = $value;
            }
            
            $totalCount = $manageobj->getCountByColumnSearch($column, $columnValue);
            if ($totalCount > $perPage) {
                $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
            }
        }
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('uimidinteresttaglist', $uimidinteresttaglist);
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('searchCondition', $searchCondition);
        $smartyObj->assign('searchContent', $searchContent);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('interestactive', "active");
        $smartyObj->assign('uimidinteresttaglistside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("interest/uimidinteresttaglist.html"); 
    }
}
new uimidinteresttaglist();
?>