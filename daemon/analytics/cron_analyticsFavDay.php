<?php
include_once (dirname(dirname(__FILE__)) . "/DaemonBase.php");
class cron_analyticsFavDay extends DaemonBase {
    public $isWhile = false;
    protected function deal() {
        $todocounttime = time() - 720;
        $day = date('Y-m-d', $todocounttime);
        $timeline = date('Ymd', $todocounttime);
        
        $alislsobj = new AliSlsUserActionLog();
        $list = $alislsobj->favAlbumCountDay($day);
        
        $personnum = $list['usercount'];
        $favnum = $list['favcount'];
        $analytics = new Analytics();
        $flag = $analytics->putAnalyticsFavDay($timeline, $personnum, $favnum);
    }
    
    protected function checkLogPath() {
    }

}
new cron_analyticsFavDay();