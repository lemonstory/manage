<?php
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_analyticsFavHour extends DaemonBase {
	public $isWhile = false;
	protected function deal() {
		$analytics = new Analytics();
		$db = DbConnecter::connectMysql('share_main');
		$todocounttime = time()-720;
		$hour = date('Y-m-d H',$todocounttime);
		
		$sql = "select count(distinct(uid)) as pn,count(1) as tn from fav_album where addtime like ? limit 1;";
		$st = $db->prepare($sql);
		$st->execute(array($hour.'%'));
		$list = $st->fetch(PDO::FETCH_ASSOC);

		
		$timeline = date('YmdH',$todocounttime);
		$personnum = $list['pn'];
		$favnum = $list['tn'];
	    $flag = $analytics->putAnalyticsFavHour($timeline, $personnum, $favnum);
		echo "$hour fav update flag:$flag\n";
	}

	protected function checkLogPath() {}

}
new cron_analyticsFavHour ();