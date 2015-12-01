<?php
include_once (dirname(dirname(__FILE__)) . "/DaemonBase.php");
class cron_analyticsRegNum extends DaemonBase {
    public $isWhile = false;
    protected function deal() {
        $todocounttime = time() - 720;
        $day = date('Y-m-d', $todocounttime);
        $hour = date('H', $todocounttime);
        $timeline = date('YmdH', $todocounttime);
        
        $alislsobj = new AliSlsUserActionLog();
        $personnum = $alislsobj->registerCountHour($day, $hour);
        
        $analytics = new Analytics();
        $flag = $analytics->putAnalyticsPassportHour($timeline, $personnum);
    }
    
    protected function checkLogPath() {
    }

}
new cron_analyticsRegNum();