<?php
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_analyticsCommentHour extends DaemonBase {
	public $isWhile = false;
	protected function deal() {
		$analytics = new Analytics();
		$db = DbConnecter::connectMysql('share_comment');
		$todocounttime = time()-720;
		$hour = date('Y-m-d H',$todocounttime);
		
		$sql = "select count(distinct(userid)) as pn,count(1) as tn from album_comment where addtime like ? limit 1;";
		$st = $db->prepare($sql);
		$st->execute(array($hour.'%'));
		$list = $st->fetch(PDO::FETCH_ASSOC);
		
		$timeline = date('YmdH', $todocounttime);
		$personnum = $list['pn'];
		$commentnum = $list['tn'];
	    $flag = $analytics->putAnalyticsCommentHour($timeline, $personnum, $commentnum);
		echo "$hour comment update flag:$flag";
	}

	protected function checkLogPath() {}

}
new cron_analyticsCommentHour ();