<?php
/*
 * 统计相关进程启动程序
 */
include 'DaemonBase.php';
class cron_analytcsBooter extends DaemonBase {
	public $isWhile                            = false;
	protected function deal() {
		$processlist    = $this->getProcessList();
		foreach($processlist as $process) {
			$this->startpro($process);
		}
		exit();
	}
	private function getProcessList()
	{
		$list = array(
		        /*
		        'analytics/cron_analyticsCommentDay.php',
		        'analytics/cron_analyticsCommentNum.php',
		        'analytics/cron_analyticsDownDay.php',
		        'analytics/cron_analyticsDownNum.php',
		        'analytics/cron_analyticsFavDay.php',
		        'analytics/cron_analyticsFavNum.php',
		        'analytics/cron_analyticsListenDay.php',
		        'analytics/cron_analyticsListenNum.php',
		        'analytics/cron_analyticsRegNum.php'
		        
		        */
		        );
		return $list;
	}
	protected function checkLogPath() {

	}
}
new cron_analytcsBooter();