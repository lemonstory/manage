<?php
include_once (dirname(dirname(__FILE__)) . "/DaemonBase.php");
class cron_analyticsRegDay extends DaemonBase {
    public $isWhile = false;
    protected function deal() {
        $todocounttime = time() - 720;
        $day = date('Y-m-d', $todocounttime);
        $timeline = date('Ymd', $todocounttime);
        
        $alislsobj = new AliSlsUserActionLog();
        $list = $alislsobj->registerCountDay($day);
        
        $personnum = $list['regcount'];
        $analytics = new Analytics();
        $flag = $analytics->putAnalyticsPassportDay($timeline, $personnum);
    }
    
    protected function checkLogPath() {
    }

}
new cron_analyticsRegDay();