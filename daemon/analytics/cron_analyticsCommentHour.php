<?php
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_analyticsCommentNum extends DaemonBase {
	public $isWhile = false;
	protected function deal() {
		$analytics = new Analytics();
		$db = DbConnecter::connectMysql('share_comment');
		$todocounttime = time()-720;
		$day = date('Y-m-d H',$todocounttime);
		$sql = "select count(distinct(uid)) as pn,count(1) as tn from album_comment where addtime like ? limit 1;";
		$st = $db->prepare($sql);
		$st->execute(array($day.'%'));
		$list = $st->fetch(PDO::FETCH_ASSOC);
		
		$timeline = date('YmdH',$todocounttime);
		$personnum = $list['pn'];
		$topicnum = $list['tn'];
	    $flag = $analytics->putanalyticscomment($timeline, $personnum, $topicnum);
		echo "$day comment update flag:$flag";
	}

	protected function checkLogPath() {}

}
new cron_analyticsCommentNum ();