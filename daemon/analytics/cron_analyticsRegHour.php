<?php
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_analyticsRegNum extends DaemonBase {
	public $isWhile = false;
	protected function deal() {
		$analytics = new Analytics();
		$db = DbConnecter::connectMysql('share_main');
		$todocounttime = time()-720;
		$day = date('Y-m-d H',$todocounttime);
		$sql = "select count(1) as pn from passport where addtime like ? limit 1;";
		$st = $db->prepare($sql);
		$st->execute(array($day.'%'));
		$list = $st->fetch(PDO::FETCH_ASSOC);

		$timeline = date('YmdH',$todocounttime);
		$personnum = $list['pn'];
	    $flag = $analytics->putanalyticspassport($timeline, $personnum);
		echo "$day passport update flag:$flag";
	}

	protected function checkLogPath() {}

}
new cron_analyticsRegNum ();