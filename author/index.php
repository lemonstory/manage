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
        $author_uid = $this->getRequest('authorUid', '');
        $nickname = $this->getRequest('nickname', "");
        $online_status   = $this->getRequest('online_status', '');

        $authorList = $whereArr = array();
        $totalCount = 0;
        $whereArr = array(
            "is_author" => 1,
        );

        if (empty($currentPage)) {
            $currentPage = 0;
        }
        if (empty($perPage)) {
            $perPage = 20;
        }
        if(!empty($nickname)) {
            $nickname = trim($nickname);
            $whereArr['nickname'] = "%{$nickname}%";
        }
        if(strcmp($online_status,"") != 0) {
            $online_status = intval($online_status);
            $whereArr['online_status'] = "{$online_status}";
        }
        
        $creator = new Creator();
        $totalCount = $creator->getCreatorCount($whereArr);
        $authorList = $creator->getCreatorList($whereArr, $currentPage + 1, $perPage);

        $pageBanner = "";
        $baseUri = "/author/index.php?author_uid={$author_uid}&nickname={$nickname}&online_status={$online_status}";
        if ($totalCount > $perPage) {
            $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('nickname', $nickname);
        $smartyObj->assign('online_status', $online_status);
        $smartyObj->assign('authorList', $authorList);
        $smartyObj->assign('authoractive', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("author/index.html");
    }
}

new index();