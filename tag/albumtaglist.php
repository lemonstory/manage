<?php
include_once '../controller.php';

class albumtaglist extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $albumTagList = $where = array();

        $tagId   = $this->getRequest('tag_id', '');

        if (empty($currentPage)) {
            $currentPage = 0;
        }
        if (empty($perPage)) {
            $perPage = 20;
        }

        if ($tagId) {
            //$search_filter['from'] = $from;
            $where[] = "`tagid` ='{$tagId}' ";
        }

        // where 处理
        if ($where) {
            $where = implode(" AND ", $where);
        }

        $pageBanner = "";
        $baseUri = "/tag/albumtaglist.php?tag_id={$tagId}";

        $manageAlbumTagRelationObj = new ManageAlbumTagRelation();

        $totalCount = $manageAlbumTagRelationObj->getAlbumTagRelationTotalCount($where);
        if ($totalCount) {
            $albumTagList = $manageAlbumTagRelationObj->getAlbumListByTagId($where, $currentPage + 1, $perPage);
        }
        //var_dump($albumTagList);
        if (count($albumTagList)) {
            foreach ($albumTagList as $k => $v) {

            }
        }
        if ($totalCount > $perPage) {
            $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('albumTagList', $albumTagList);
        $smartyObj->assign('tagactive', "active");
        $smartyObj->assign('albumtaglistside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("tag/albumtaglist.html");
    }
}
new albumtaglist();
?>