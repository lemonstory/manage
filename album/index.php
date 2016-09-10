<?php
include_once '../controller.php';

class index extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $albumList = $where = array();
        
        if (empty($currentPage)) {
            $currentPage = 0;
        } 
        if (empty($perPage)) {
            $perPage = 20;
        }

        $albumid = $this->getRequest('albumid', '');
        $title   = $this->getRequest('title', '');
        $from   = $this->getRequest('from', '');

        $search_filter = array();

        if ($title) {
            $search_filter['title'] = $title;
            $where[] = "`title` like '%{$title}%' ";
        }
        if ($albumid) {
            $search_filter['albumid'] = $albumid;
            $where[] = "`id` ='{$albumid}' ";
        }
        if ($from) {
            $search_filter['from'] = $from;
            $where[] = "`from` ='{$from}' ";
        }

        $pageBanner = "";
        $baseUri = "/album/index.php?albumid={$albumid}&title={$title}&from={$from}";
        
        $manageAlbumObj = new ManageAlbum();
        // where 处理
        if ($where) {
            $where = implode(" AND ", $where);
        }
        $totalCount = $manageAlbumObj->getAlbumTotalCount($where);
        if ($totalCount) {
            $albumList = $manageAlbumObj->getAlbumList($where, $currentPage + 1, $perPage);
        }

        if (count($albumList)) {
            $aliossobj = new AliOss();
            $albumids = array();
            foreach ($albumList as $k => $v) {
                $albumids[] = $v['id'];
            }
            // 获取多个专辑的关联tag列表
            $tagnewobj = new TagNew();
            $relationlist = $tagnewobj->getAlbumTagRelationListByAlbumIds($albumids);
            $tagids = array();
            if (!empty($relationlist)) {
                foreach ($relationlist as $albumid => $albumtaglist) {
                    foreach ($albumtaglist as $value) {
                        $tagids[] = $value['tagid'];
                    }
                }
            }
            $tagids = array_unique($tagids);
            // 获取标签信息
            $taglist = $tagnewobj->getTagInfoByIds($tagids);
            
            foreach ($albumList as $k => $v) {
                if ($v['cover']) {
                    $albumList[$k]['cover'] = $aliossobj->getImageUrlNg($aliossobj->IMAGE_TYPE_ALBUM, $v['cover'], 100, $v['cover_time']);
                } else {
                    $albumList[$k]['cover'] = $v['s_cover'];
                }
                // 整合专辑的所有标签
                $albumtaglist = array();
                $albumtaglist = $relationlist[$v['id']];
                if (empty($albumtaglist)) {
                    continue;
                }
                foreach ($albumtaglist as $relationinfo) {
                    $tagid = $relationinfo['tagid'];
                    if (empty($taglist[$tagid])) {
                        continue;
                    }
                    $albumList[$k]['taglist'][$tagid] = $taglist[$tagid];
                }
            }
        }
        
        if ($totalCount > $perPage) {
            $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('search_filter', $search_filter);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('albumList', $albumList);
        $smartyObj->assign('albumactive', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("album/index.html"); 
    }
}
new index();
?>