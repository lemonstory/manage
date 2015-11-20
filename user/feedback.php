<?php
include_once '../controller.php';

class index extends controller
{
    public function action()
    {
        $currentPage = $this->getRequest('p') + 0;
        $perPage  = $this->getRequest('perPage', 20) + 0;
        $feedbackid    = (int)$this->getRequest('feedbackid', 0);
        $status   = (int)$this->getRequest('status', 1);
        $keywords = $this->getRequest('keywords', '');
        $reply    = (int)$this->getRequest('reply', -1);

        $replylist = $search_filter = $where = array();

        if (empty($currentPage)) {
            $currentPage = 0;
        } 
        if (empty($perPage)) {
            $perPage = 20;
        }

        $where['status']  =  $status;

        if ($reply != -1) {
            if ($reply == 0) {
                $where[] = "`replyid` = 0";
            } else {
                $where[] = "`replyid` > 0";
            }
            
        } else {
            $where[] = "`replyid` = 0";
        }

        if ($keywords) {
            $search_filter['keywords'] = $keywords;
            $where[] = " `content` like '%{$keywords}%' ";
        }
        
        $pageBanner = "";
        $baseUri = "/user/feedback.php?perPage={$perPage}&";
        
        
        $ssoList = array();
        $ssoObj = new Sso();
        
        $manageUserFeedback = new ManageUserFeedback();
        if ($where) {
            $where = implode(" AND ", $where);
        }
        $feedbackList = $manageUserFeedback->getUserFeedbackList($where, $currentPage + 1, $perPage);

        $totalCount = $manageUserFeedback->getUserFeedbackTotalCount($where);
        
        if ($totalCount > $perPage) {
            $pageBanner = Page::NumeralPager($currentPage, ceil($totalCount/$perPage), $baseUri, $totalCount);
        }

        if ($feedbackList) {
            $ids = array();
            foreach($feedbackList as $k => $v) {
                array_push($ids, $v['id']);
            }
            if ($ids) {
                $replylist = $manageUserFeedback->getByFeedbackId($ids);
                foreach ($feedbackList as $k => $v) {
                    if (isset($replylist[$v['id']])) {
                        $feedbackList[$k]['reply'] = $replylist[$v['id']];
                    }
                }
            }
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('totalCount', $totalCount);
        $smartyObj->assign('p', $currentPage);
        $smartyObj->assign('replylist', $replylist);
        $smartyObj->assign('perPage', $perPage);
        $smartyObj->assign('search_filter', $search_filter);
        $smartyObj->assign('pageBanner', $pageBanner);
        $smartyObj->assign('feedbackList', $feedbackList);
        $smartyObj->assign('feedbackactive', "active");
        $smartyObj->assign('useractive', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("user/feedbacklist.html"); 
    }
}
new index();
?>