<?php
/*
 * 统计相关进程启动程序
 * 每半小时跑一次
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
		        'analytics/cron_analyticsCommentDay.php',
		        'analytics/cron_analyticsCommentHour.php',
		        'analytics/cron_analyticsDownDay.php',
		        'analytics/cron_analyticsDownHour.php',
		        'analytics/cron_analyticsFavDay.php',
		        'analytics/cron_analyticsFavHour.php',
		        'analytics/cron_analyticsListenDay.php',
		        'analytics/cron_analyticsListenHour.php',
		        'analytics/cron_analyticsRegHour.php',
		        'analytics/cron_analyticsRegDay.php'
		        );
		return $list;
	}
	protected function checkLogPath() {

	}
}
new cron_analytcsBooter();