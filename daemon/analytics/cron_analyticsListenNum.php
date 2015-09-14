<?php
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_analyticsListenNum extends DaemonBase {
	public $isWhile = false;
	protected function deal() {
		$analytics = new Analytics();
		$db = DbConnecter::connectMysql('share_main');
		$todocounttime = time()-720;
		$day = date('Y-m-d H',$todocounttime);
		
		$sql = "select count(distinct(uid)) as pn,count(1) as tn from user_listen where addtime like ? limit 1;";
		$st = $db->prepare($sql);
		$st->execute(array($day.'%'));
		$list = $st->fetch(PDO::FETCH_ASSOC);

		
		$timeline = date('YmdH',$todocounttime);
		$personnum = $list['pn'];
		$listenum = $list['tn'];
	    $flag = $analytics->putanalyticslisten($timeline, $personnum, $listenum);
		echo "$day listen update flag:$flag\n";
	}

	protected function checkLogPath() {}

}
new cron_analyticsListenNum ();