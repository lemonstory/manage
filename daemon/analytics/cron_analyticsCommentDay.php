<?php
include_once (dirname(dirname(__FILE__)) . "/DaemonBase.php");
class cron_analyticsCommentDay extends DaemonBase {
    public $isWhile = false;
    protected function deal() {
        $todocounttime = time() - 720;
        $day = date('Y-m-d', $todocounttime);
        $timeline = date('Ymd', $todocounttime);
        
        $alislsobj = new AliSlsUserActionLog();
        $list = $alislsobj->commentAlbumCountDay($day);
        
        $personnum = $list['usercount'];
        $commentnum = $list['commentcount'];
        $actionalbumnum = $list['albumcount'];
        $analytics = new Analytics();
        $flag = $analytics->putAnalyticsCommentDay($timeline, $personnum, $commentnum, $actionalbumnum);
    }
    
    protected function checkLogPath() {
    }

}
new cron_analyticsCommentDay();