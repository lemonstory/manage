<?php
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_analyticsDownDay extends DaemonBase {
	public $isWhile = false;
	protected function deal() {
		$analytics = new Analytics();
		$db = DbConnecter::connectMysql('share_main');
		$todocounttime = time()-720;
		$day = date('Y-m-d',$todocounttime);
		
		$sql = "select count(distinct(uid)) as pn,count(1) as tn from user_download where addtime like ? limit 1;";
		$st = $db->prepare($sql);
		$st->execute(array($day.'%'));
		$list = $st->fetch(PDO::FETCH_ASSOC);
	
		
		$timeline = date('Ymd',$todocounttime);
		$personnum = $list['pn'];
		$downnum = $list['tn'];
	    $flag = $analytics->putanalyticsdownday($timeline, $personnum, $downnum);
		echo "$day down day update flag:$flag \n";
	}

	protected function checkLogPath() {}

}
new cron_analyticsDownDay();