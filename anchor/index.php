<?php
/**
 * 主播列表
 * Date: 16/10/7
 * Time: 下午4:18
 */

include_once '../controller.php';

class index extends controller
{
    public function action()
    {

        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $anchorList = $whereArr = array();
        $totalCount = 0;

        if (empty($currentPage)) {
            $currentPage = 0;
        }
        if (empty($perPage)) {
            $perPage = 20;
        }

        $anchor_uid = $this->getRequest('anchorUid', '');
        $name   = $this->getRequest('name', '');
        $status   = $this->getRequest('status', '1');
        
        $creator = new Creator();
        $whereArr = array(
            "is_anchor" => 1,
        );

        $totalCount = $creator->getCreatorCount($whereArr);
        $anchorList = $creator->getCreatorList($whereArr, $currentPage + 1, $perPage);

        $pageBanner = "";
        $baseUri = "/anchor/index.php?anchor_uid={$anchor_uid}&name={$name}&status={$status}";
        if ($totalCount > $perPage) {
            $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('anchorList', $anchorList);
        $smartyObj->assign('anchoractive', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("anchor/index.html");
    }
}

new index();