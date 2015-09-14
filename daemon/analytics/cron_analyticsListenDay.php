<?php
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_analyticsListenDay extends DaemonBase {
	public $isWhile = false;
	protected function deal() {
		$analytics = new Analytics();
		$db = DbConnecter::connectMysql('share_main');
		$todocounttime = time()-720;
		$day = date('Y-m-d',$todocounttime);
		
		$sql = "select count(distinct(uid)) as pn,count(1) as tn from user_listen where addtime like ? limit 1;";
		$st = $db->prepare($sql);
		$st->execute(array($day.'%'));
		$list = $st->fetch(PDO::FETCH_ASSOC);
	
		
		$timeline = date('Ymd',$todocounttime);
		$personnum = $list['pn'];
		$listennum = $list['tn'];
	    $flag = $analytics->putanalyticslistenday($timeline, $personnum, $listennum);
		echo "$day listen day update flag:$flag \n";
	}

	protected function checkLogPath() {}

}
new cron_analyticsListenDay();