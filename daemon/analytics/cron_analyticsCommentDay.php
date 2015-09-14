<?php
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_analyticsCommentDay extends DaemonBase {
	public $isWhile = false;
	protected function deal() {
		$analytics = new Analytics();
		$db = DbConnecter::connectMysql('share_comment');
		$todocounttime = time()-720;
		$day = date('Y-m-d',$todocounttime);
		$sql = "select count(distinct(uid)) as pn,count(1) as tn,count(distinct(albumid)) as atn from album_comment where addtime like ? limit 1;";
		$st = $db->prepare($sql);
		$st->execute(array($day.'%'));
		$list = $st->fetch(PDO::FETCH_ASSOC);
		
		$timeline = date('Ymd',$todocounttime);
		$personnum = $list['pn'];
		$topicnum = $list['tn'];
		$actionalbumnum = $list['atn'];
	    $flag = $analytics->putanalyticscommentday($timeline, $personnum, $topicnum, $actionalbumnum);
		echo "$day comment day update flag:$flag";
	}

	protected function checkLogPath() {}

}
new cron_analyticsCommentDay ();