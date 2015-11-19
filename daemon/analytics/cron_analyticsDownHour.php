<?php
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_analyticsDownHour extends DaemonBase {
	public $isWhile = false;
	protected function deal() {
		$analytics = new Analytics();
		$db = DbConnecter::connectMysql('share_main');
		$todocounttime = time() - 720;
		$hour = date('Y-m-d H', $todocounttime);
		
		$sql = "select count(distinct(uimid)) as pn,count(1) as tn from download_story where addtime like ? limit 1;";
		$st = $db->prepare($sql);
		$st->execute(array($hour.'%'));
		$list = $st->fetch(PDO::FETCH_ASSOC);

		
		$timeline = date('YmdH', $todocounttime);
		$personnum = $list['pn'];
		$downnum = $list['tn'];
	    $flag = $analytics->putAnalyticsDownHour($timeline, $personnum, $downnum);
		echo "$hour down update flag:$flag\n";
	}

	protected function checkLogPath() {}

}
new cron_analyticsDownHour ();