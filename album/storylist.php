<?php
include_once '../controller.php';

class index extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $albumid = (int)$this->getRequest('albumid', 0);
        $storyid = (int)$this->getRequest('storyid', 0);
        $title   = $this->getRequest('title', '');

        $storyList = $search_filter = $where = array();

        if (empty($currentPage)) {
            $currentPage = 0;
        } 
        if (empty($perPage)) {
            $perPage = 20;
        }

        $pageBanner = "";
        $baseUri = "/album/storylist.php?perPage={$perPage}&";

        if ($albumid) {
            $search_filter['albumid'] = $albumid;
            $where[] = " `album_id` = '{$albumid}' ";
            $baseUri =  $baseUri +  "album_id={$albumid}&";
        }

        if ($storyid) {
            $search_filter['storyid'] = $storyid;
            $where[] = " `id` = '{$storyid}' ";
            $baseUri =  $baseUri +  "storyid={$storyid}&";
        }
        if ($title) {
            $search_filter['title'] = $title;
            $where[] = " `title` like '%{$title}%' ";
            $baseUri =  $baseUri +  "title={$title}&";
        }
        
        $ssoList = array();
        $ssoObj = new Sso();
        
        $manageStoryObj = new ManageStory();
        if ($where) {
            $where = implode(" AND ", $where);
        }

        $totalCount = $manageStoryObj->getStoryTotalCount($where);
        if ($totalCount) {
            $aliossobj = new AliOss();
            $storyList = $manageStoryObj->getStoryList($where, $currentPage + 1, $perPage);
            foreach ($storyList as $k => $v) {
                if ($v['cover']) {
                    $storyList[$k]['cover'] = $aliossobj->getImageUrlNg($v['cover'], 200);
                } else {
                    $storyList[$k]['cover'] = $v['s_cover'];
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
        $smartyObj->assign('storyList', $storyList);
        $smartyObj->assign('storyactive', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("album/story_list.html"); 
    }
}
new index();
?>