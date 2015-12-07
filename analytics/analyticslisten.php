<?php
include_once '../controller.php';
class analyticslisten extends controller 
{
    public function action()
    {
        $showflag = $this->getRequest('showflag');
        if (empty($showflag)) {
            $showflag = 'daylistennum';
        }
        $stime = $this->getRequest('stime');
        $etime = $this->getRequest('etime');
        if (empty($etime)) {
            $etime = date("Y-m-d", time());
        }
        if (empty($stime)) {
            $stime = date("Y-m-d", time() - 30 * 86400);
        }
        $showtype = 'listen';
        
        $analytics = new Analytics();
        if ($showflag == 'daylistennum') {
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
        $smartyobj->assign('analyticslistenside', 'active');
        $smartyobj->assign("headerdata", $this->headerCommonData());
        $smartyobj->display('analytics/analyticslisten.html');
    }
}
new analyticslisten();
?>