<?php
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_analyticsFavNum extends DaemonBase {
	public $isWhile = false;
	protected function deal() {
		$analytics = new Analytics();
		$db = DbConnecter::connectMysql('share_main');
		$todocounttime = time()-720;
		$day = date('Y-m-d H',$todocounttime);
		
		$sql = "select count(distinct(uid)) as pn,count(1) as tn from user_fav where addtime like ? limit 1;";
		$st = $db->prepare($sql);
		$st->execute(array($day.'%'));
		$list = $st->fetch(PDO::FETCH_ASSOC);

		
		$timeline = date('YmdH',$todocounttime);
		$personnum = $list['pn'];
		$favnum = $list['tn'];
	    $flag = $analytics->putanalyticsfav($timeline, $personnum, $favnum);
		echo "$day fav update flag:$flag\n";
	}

	protected function checkLogPath() {}

}
new cron_analyticsFavNum ();