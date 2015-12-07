<?php
include_once '../controller.php';
class analyticsfav extends controller 
{
    public function action() 
    {
        $showflag = $this->getRequest('showflag');
        if (empty($showflag)) {
            /*
             * ndaylist    n天内的曲线图 
             */
            $showflag = 'ndaylist';
        }
        $stime = $this->getRequest('stime');
        $etime = $this->getRequest('etime');
        if (empty($etime)) {
            $etime = date("Ymd", time());
        }
        if (empty($stime)) {
            $stime = date("Ymd", time() - 30 * 86400);
        }
        $showtype = 'fav';
        
        $analytics = new Analytics();
        if ($showflag == 'ndaylist') {
            $list = $analytics->getAnalyticsDayList($showtype, $stime, $etime);
        }
        var_dump($list);
        
        $smartyobj = $this->getSmartyObj();
        $smartyobj->assign('showflag', $showflag);
        $smartyobj->assign('stime', $stime);
        $smartyobj->assign('etime', $etime);
        $smartyobj->assign("headerdata", $this->headerCommonData());
        $smartyobj->display('analytics/analyticsfav.html');
    }
}

new analyticsfav();
?>