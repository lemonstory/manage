<?php
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_analyticsListenDay extends DaemonBase {
	public $isWhile = false;
	protected function deal() {
		$analytics = new Analytics();
		$db = DbConnecter::connectMysql('share_main');
		$todocounttime = time() - 720;
		$day = date('Y-m-d', $todocounttime);
		$starttime = strtotime($day . " 00:00:00");
		$endtime = strtotime($day . " 23:59:59");
		
		$sql = "select count(distinct(uimid)) as pn,count(1) as tn from `listen_story` where `uptime` > ? and `uptime` < ? limit 1;";
		$st = $db->prepare($sql);
		$st->execute(array($starttime, $endtime));
		$list = $st->fetch(PDO::FETCH_ASSOC);
	
		
		$timeline = date('Ymd', $todocounttime);
		$personnum = $list['pn'];
		$listennum = $list['tn'];
	    $flag = $analytics->putAnalyticsListenDay($timeline, $personnum, $listennum);
		echo "$day listen day update flag:$flag \n";
	}

	protected function checkLogPath() {}

}
new cron_analyticsListenDay();