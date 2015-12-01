<?php
include_once (dirname(dirname(__FILE__)) . "/DaemonBase.php");
class cron_analyticsDownHour extends DaemonBase {
    public $isWhile = false;
    protected function deal() {
        $todocounttime = time() - 720;
        $day = date('Y-m-d', $todocounttime);
        $hour = date('H', $todocounttime);
        $timeline = date('YmdH', $todocounttime);
        
        $alislsobj = new AliSlsUserActionLog();
        $downnum = $alislsobj->downloadStoryCountHour($day, $hour);
        
        $personnum = 0;
        $analytics = new Analytics();
        $flag = $analytics->putAnalyticsDownHour($timeline, $personnum, $downnum);
    }
    
    protected function checkLogPath() {
    }

}
new cron_analyticsDownHour();