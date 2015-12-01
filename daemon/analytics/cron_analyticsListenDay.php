<?php
include_once (dirname(dirname(__FILE__)) . "/DaemonBase.php");
class cron_analyticsListenDay extends DaemonBase {
    public $isWhile = false;
    protected function deal() {
        $todocounttime = time() - 720;
        $day = date('Y-m-d', $todocounttime);
        $timeline = date('Ymd', $todocounttime);
        
        $alislsobj = new AliSlsUserActionLog();
        $list = $alislsobj->listenStoryCountDay($day);
        
        $personnum = $list['usercount'];
        $listennum = $list['listennum'];
        $analytics = new Analytics();
        $flag = $analytics->putAnalyticsListenDay($timeline, $personnum, $listennum);
    }
    
    protected function checkLogPath() {
    }

}
new cron_analyticsListenDay();