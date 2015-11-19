<?php
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_analyticsListenHour extends DaemonBase {
	public $isWhile = false;
	protected function deal() {
		$analytics = new Analytics();
		$db = DbConnecter::connectMysql('share_main');
		$todocounttime = time() - 720;
		$hour = date('Y-m-d H', $todocounttime);
		$starthourtime = strtotime($hour . ":00:00");
		$endhourtime = strtotime($hour . ":59:59");
		
		$sql = "select count(distinct(uimid)) as pn,count(1) as tn from `listen_story` where `uptime` > ? and `uptime` < ? limit 1;";
		$st = $db->prepare($sql);
		$st->execute(array($starthourtime, $endhourtime));
		$list = $st->fetch(PDO::FETCH_ASSOC);

		
		$timeline = date('YmdH', $todocounttime);
		$personnum = $list['pn'];
		$listenum = $list['tn'];
	    $flag = $analytics->putAnalyticsListenHour($timeline, $personnum, $listenum);
		echo "$day listen update flag:$flag\n";
	}

	protected function checkLogPath() {}

}
new cron_analyticsListenHour ();