<?php
include_once '../controller.php';
class analyticsfav extends controller 
{
    public function action() 
    {
        $showflag = $this->getRequest('showflag');
        if (empty($showflag)) {
            /*
             * dayfavnum    n天内的收藏量曲线图 
             */
            $showflag = 'dayfavnum';
        }
        $stime = $this->getRequest('stime');
        $etime = $this->getRequest('etime');
        if (empty($etime)) {
            $etime = date("Y-m-d", time());
        }
        if (empty($stime)) {
            $stime = date("Y-m-d", time() - 30 * 86400);
        }
        $showtype = 'fav';
        
        $analytics = new Analytics();
        if ($showflag == 'dayfavnum') {
            $reslist = $analytics->getAnalyticsDayList($showtype, $stime, $etime);
        }
        
        $data = "";
        foreach ($reslist as $value) {
            $value['timeline'] = date("Y-m-d", strtotime($value['timeline']));
            $data[] = array("period" => "{$value['timeline']}", "num" => $value['totalnum']+0);
        }
        $data = json_encode($data);
        
        $smartyobj = $this->getSmartyObj();
        $smartyobj->assign('showflag', $showflag);
        $smartyobj->assign('stime', $stime);
        $smartyobj->assign('etime', $etime);
        $smartyobj->assign('list', $list);
        $smartyobj->assign('data', $data);
        $smartyobj->assign('analyticsactive', "active");
        $smartyobj->assign('analyticsfavside', 'active');
        $smartyobj->assign("headerdata", $this->headerCommonData());
        $smartyobj->display('analytics/analyticsfav.html');
    }
}

new analyticsfav();
?>