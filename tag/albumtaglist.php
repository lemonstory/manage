<?php
include_once '../controller.php';

class albumtaglist extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $searchCondition = $this->getRequest('searchCondition', '');
        $searchContent = $this->getRequest('searchContent', '');

        $albumTagList = $where = $tagInfo = array();

        $tagId   = $this->getRequest('tag_id', '');
        $manageTagInfoObj = new ManageTagInfo();

        if($searchCondition == 'tag_id'){
            $tagId = $searchContent;
            $tagInfo = $manageTagInfoObj->getTagInfoById($tagId);
        }elseif($searchCondition == 'tag_name'){
            $tagInfo = $manageTagInfoObj->getTagInfoByName($searchContent);
            $tagId = $tagInfo['id'];
        }else{
            $searchContent = $tagId;
            $searchCondition = 'tag_id';
            if ($tagId) {
                $tagInfo = $manageTagInfoObj->getTagInfoById($tagId);
            }
        }

        if (empty($currentPage)) {
            $currentPage = 0;
        }
        if (empty($perPage)) {
            $perPage = 20;
        }

        if ($tagId) {
            $where['tagid'] = $tagId;
        }

        $pageBanner = "";
        $baseUri = "/tag/albumtaglist.php?tag_id={$tagId}";

        $manageAlbumTagRelationObj = new ManageAlbumTagRelation();

        $totalCount = $manageAlbumTagRelationObj->getAlbumTagRelationTotalCount($where);
        if ($totalCount) {
            $albumTagList = $manageAlbumTagRelationObj->getAlbumListByTagId($where, $currentPage + 1, $perPage);
        }
        if ($totalCount > $perPage) {
            $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('searchCondition', $searchCondition);
        $smartyObj->assign('searchContent', $searchContent);
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('tagInfo', $tagInfo);
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