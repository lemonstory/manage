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
    	$recommenddesclist = array();
    	if (!empty($searchContent)) {
    	    $column = $searchCondition;
            $columnValue = $searchContent;
        } else {
            $column = $columnValue = '';
        }
        $managesysobj = new ManageSystem();
        $resultList = $managesysobj->getRecommendListByColumnSearch("share_story", "recommend_hot", $column, $columnValue, $status, $currentPage + 1, $perPage);
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
                
                // 获取推荐语
                $recommenddescobj = new RecommendDesc();
                $recommenddesclist = $recommenddescobj->getAlbumRecommendDescList($albumids);
            }
            
            // 获取多个专辑的关联tag列表
            $tagnewobj = new TagNew();
            $relationlist = $tagnewobj->getAlbumTagRelationListByAlbumIds($albumids);
            $tagids = array();
            if (!empty($relationlist)) {
                foreach ($relationlist as $albumid => $albumtaglist) {
                    if (!empty($albumtaglist)) {
                        foreach ($albumtaglist as $value) {
                            $tagids[] = $value['tagid'];
                        }
                    }
                }
            }
            $tagids = array_unique($tagids);
            // 获取标签信息
            $taglist = $tagnewobj->getTagInfoByIds($tagids);
            
            $aliossobj = new AliOss();
            foreach ($resultList as $value) {
                $albumid = $value['albumid'];
                if (empty($albumlist[$albumid])) {
                    continue;
                }
                $albuminfo = $albumlist[$albumid];
                if (!empty($albuminfo['cover'])) {
                    $albuminfo['cover'] = $aliossobj->getImageUrlNg($aliossobj->IMAGE_TYPE_ALBUM, $albuminfo['cover'], 100, $albuminfo['cover_time']);
                }
                
                // 整合专辑的所有标签、推荐的标签
                $recommendtaglist = array();
                if (!empty($relationlist[$albumid])) {
                    foreach ($relationlist[$albumid] as $relationinfo) {
                        $tagid = $relationinfo['tagid'];
                        if (!empty($taglist[$tagid])) {
                            $albuminfo['taglist'][$tagid] = $taglist[$tagid];
                        }
                        if ($relationinfo['isrecommend'] == 1) {
                            $albuminfo['recommendtaglist'][$tagid] = $taglist[$tagid];
                        }
                    }
                }
                
                $albuminfo['recommenddesc'] = "";
                if (!empty($recommenddesclist[$albumid])) {
                    $albuminfo['recommenddesc'] = $recommenddesclist[$albumid]['desc'];
                }
                
                $value['albuminfo'] = $albuminfo;
                $hotlist[] = $value;
            }
            
            $totalCount = $managesysobj->getRecommendCountByColumnSearch("share_story", "recommend_hot", $column, $columnValue, $status);
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