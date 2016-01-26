<?php
include_once '../controller.php';

class gettaglist extends controller
{
    public function action()
    {
        // 用于添加专辑标签参数
        $displaycheckbox = $this->getRequest('displaycheckbox', 0);
        $albumid = $this->getRequest('albumid', 0);
        
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
        $baseUri = "/tag/gettaglist.php?displaycheckbox={$displaycheckbox}&albumid={$albumid}&perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        $totalCount = 0;
        $taglist = array();
        $firsttaglist = array();
        $secondtaglist = array();
        
        // 添加专辑标签，选中专辑已有的标签checkbox
        $checktagids = array();
        if (!empty($displaycheckbox) && !empty($albumid)) {
            $tagnewobj = new TagNew();
            $relationlist = current($tagnewobj->getAlbumTagRelationListByAlbumIds($albumid));
            if (!empty($relationlist)) {
                foreach ($relationlist as $value) {
                    $checktagids[] = $value['tagid'];
                }
            }
            if (!empty($checktagids)) {
                $checktagids = array_unique($checktagids);
            }
        }
        
        
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
                    
                    // 添加专辑标签时，一级标签的checkbox是否选中状态
                    if (!empty($checktagids) && in_array($firsttagid, $checktagids)) {
                        $taglist[$firsttagid]['checked'] = 1;
                    } else {
                        $taglist[$firsttagid]['checked'] = 0;
                    }
                    
                    if (!empty($secondtaglist)) {
                        foreach ($secondtaglist as $secondtaginfo) {
                            $secondtagid = $secondtaginfo['id'];
                            if ($secondtaginfo['pid'] == $firsttagid) {
                                $taglist[$firsttagid]['secondtaglist'][$secondtagid] = $secondtaginfo;
                                // 添加专辑标签时，二级标签的checkbox是否选中状态
                                if (!empty($checktagids) && in_array($secondtagid, $checktagids)) {
                                    $taglist[$firsttagid]['secondtaglist'][$secondtagid]['checked'] = 1;
                                } else {
                                    $taglist[$firsttagid]['secondtaglist'][$secondtagid]['checked'] = 0;
                                }
                            }
                        }
                        
                        // 一级标签下的，二级标签数量
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
        
        $refer = "";
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $refer = $_SERVER['HTTP_REFERER'];
        }
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('refer', $refer);
        $smartyObj->assign('displaycheckbox', $displaycheckbox);
        $smartyObj->assign('albumid', $albumid);
        
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