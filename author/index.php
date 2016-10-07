<?php
/**
 *
 * Date: 16/10/7
 * Time: 下午4:21
 */


include_once '../controller.php';

class index extends controller
{
    public function action()
    {

        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $authorList = $whereArr = array();
        $totalCount = 0;

        if (empty($currentPage)) {
            $currentPage = 0;
        }
        if (empty($perPage)) {
            $perPage = 20;
        }

        $author_uid = $this->getRequest('authorUid', '');
        $name   = $this->getRequest('name', '');
        $status   = $this->getRequest('status', '1');

        $creator = new Creator();
        $whereArr = array(
            "is_author" => 1,
        );

        $totalCount = $creator->getCreatorCount($whereArr);
        $authorList = $creator->getCreatorList($whereArr, $currentPage + 1, $perPage);

        $pageBanner = "";
        $baseUri = "/author/index.php?author_uid={$author_uid}&name={$name}&status={$status}";
        if ($totalCount > $perPage) {
            $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('authorList', $authorList);
        $smartyObj->assign('authoractive', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("author/index.html");
    }
}

new index();