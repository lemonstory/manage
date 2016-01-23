<?php
include_once '../controller.php';

class gettaglist extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $searchCondition = $this->getRequest('searchCondition', 'id');
        $searchContent = $this->getRequest('searchContent', '');
        if (empty($currentPage)) {
            $currentPage = 0;
        }
        if (empty($perPage)) {
            $perPage = 20;
        }
        
        $pageBanner = "";
        $baseUri = "/tag/gettaglist.php?perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        $totalCount = 0;
        $taglist = array();
        $firsttaglist = array();
        $secondtaglist = array();
        
        $managetagnewobj = new ManageTagNew();
        $orderby = "ORDER BY `status` ASC, `ordernum` ASC, `id` ASC";
        if (!empty($searchContent)) {
            $where = "`{$searchCondition}` = '{$searchContent}'";
            $resultinfo = $managetagnewobj->getTagListByColumnSearch($where, $orderby, $currentPage + 1, $perPage);
        } else {
            $where = "`pid` = 0";
            $firsttaglist = $managetagnewobj->getTagListByColumnSearch($where, $orderby, $currentPage + 1, $perPage);
            if (!empty($firsttaglist)) {
                $firsttagids = array();
                foreach ($firsttaglist as $value) {
                    $firsttagids[] = $value['id'];
                }
                if (!empty($firsttagids)) {
                    // 二级标签
                    $firsttagids = array_unique($firsttagids);
                    $firsttagidstr = implode(",", $firsttagids);
                    $secondwhere = "`pid` IN ($firsttagidstr)";
                    $secondtaglist = $managetagnewobj->getTagListByColumnSearch($secondwhere, $orderby, 0, 1000);
                }
                
                foreach ($firsttaglist as $firsttaginfo) {
                    $firsttagid = $firsttaginfo['id'];
                    $taglist[$firsttagid] = $firsttaginfo;
                    if (!empty($secondtaglist)) {
                        foreach ($secondtaglist as $secondtaginfo) {
                            $secondtagid = $secondtaginfo['id'];
                            if ($secondtaginfo['pid'] == $firsttagid) {
                                $taglist[$firsttagid]['secondtaglist'][$secondtagid] = $secondtaginfo;
                            }
                        }
                        $taglist[$firsttagid]['secondtagcount'] = 0;
                        if (!empty($taglist[$firsttagid]['secondtaglist'])) {
                            $taglist[$firsttagid]['secondtagcount'] = count($taglist[$firsttagid]['secondtaglist']);
                        }
                    }
                }
            }
        }
        
        if (!empty($taglist)) {
            $totalCount = $managetagnewobj->getTagCountByColumnSearch($where);
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
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('taglist', $taglist);
        $smartyObj->assign('tagactive', "active");
        $smartyObj->assign('gettaglistside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("tag/gettaglist.html");
    }
}
new gettaglist();