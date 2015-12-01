<?php
include_once (dirname(dirname(__FILE__)) . "/DaemonBase.php");
class cron_analyticsDownDay extends DaemonBase {
    public $isWhile = false;
    protected function deal() {
        $todocounttime = time() - 720;
        $day = date('Y-m-d', $todocounttime);
        $timeline = date('Ymd', $todocounttime);
        
        $alislsobj = new AliSlsUserActionLog();
        $list = $alislsobj->downloadStoryCountDay($day);
        
        $personnum = $list['usercount'];
        $downnum = $list['downloadcount'];
        $analytics = new Analytics();
        $flag = $analytics->putAnalyticsDownDay($timeline, $personnum, $downnum);
    }
    
    protected function checkLogPath() {
    }

}
new cron_analyticsDownDay();