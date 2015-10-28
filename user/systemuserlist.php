<?php
/*
 * 系统生成的僵尸用户，用于发表系统评论
 */
include_once '../controller.php';
class systemuserlist extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 20) + 0;
        $status = $this->getRequest('status', 0);
        $searchCondition = $this->getRequest('searchCondition', 'searchcontent');
        $searchContent = $this->getRequest('searchContent', '');
        if (empty($currentPage)) {
            $currentPage = 0;
        } 
        if (empty($perPage)) {
            $perPage = 20;
        }
        
        $list = array();
        $totalCount = 0;
        
        if (!empty($searchContent)) {
            $column = $searchCondition;
            $columnValue = $searchContent;
        } else {
            $column = $columnValue = '';
        }
        $manageobj = new ManageUser();
        $resultlist = $manageobj->getListByColumnSearch($column, $columnValue, $status, $currentPage + 1, $perPage);
        if (!empty($resultlist)) {
            $uids = array();
            $userlist = array();
            $aliOssObj = new AliOss();
            foreach ($resultlist as $value) {
                $uids[] = $value['uid'];
            }
            $uids = array_unique($uids);
            $userobj = new User();
            $userlist = $userobj->getUserInfo($uids);
            
            foreach ($resultlist as $value) {
                $userinfo = $userlist[$value['uid']];
                $userinfo['avatar'] = $aliOssObj->getAvatarUrl($value['uid'], $value['avatartime'], 100);
                $value['userinfo'] = $userinfo;
                $list[] = $value;
            }
            
            $totalCount = $manageobj->getCountByColumnSearch($column, $columnValue, $status);
            if ($totalCount > $perPage) {
                $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
            }
        }
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('list', $list);
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('searchCondition', $searchCondition);
        $smartyObj->assign('searchContent', $searchContent);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('useractive', "active");
        $smartyObj->assign('systemuserlistside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("user/systemuserlist.html"); 
    }
}
new systemuserlist();
?>