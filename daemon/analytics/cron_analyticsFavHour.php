<?php
include_once (dirname(dirname(__FILE__)) . "/DaemonBase.php");
class cron_analyticsFavHour extends DaemonBase {
    public $isWhile = false;
    protected function deal() {
        $todocounttime = time() - 720;
        $day = date('Y-m-d', $todocounttime);
        $hour = date('H', $todocounttime);
        $timeline = date('YmdH', $todocounttime);
        
        $alislsobj = new AliSlsUserActionLog();
        $favnum = $alislsobj->favAlbumCountHour($day, $hour);
        
        $personnum = 0;
        $analytics = new Analytics();
        $flag = $analytics->putAnalyticsFavHour($timeline, $personnum, $favnum);
    }
    
    protected function checkLogPath() {
    }

}
new cron_analyticsFavHour();