<?php
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

				
			    'analytics/cron_analyticsTopicNum.php',
				'analytics/cron_analyticsDiggNum.php',
				'analytics/cron_analyticsFriendNum.php',
				'analytics/cron_analyticsCommentNum.php',
				'analytics/cron_analyticsMsgNum.php',
				'analytics/cron_analyticsRegNum.php',
				
				'analytics/cron_analyticsTopicDay.php',
				'analytics/cron_analyticsDiggDay.php',
				'analytics/cron_analyticsCommentDay.php',
				'analytics/cron_analyticsMsgDay.php',
				'analytics/cron_analyticsTopicDayAge.php',
				'analytics/cron_analyticsDoubleFriendDay.php',
				'analytics/cron_analyticsFollowDay.php',
				'analytics/cron_analyticsReposttopicDay.php',
				'analytics/cron_analyticsSignDay.php',
				'analytics/cron_analyticsHtFollowDay.php',
				
		);
		return $list;
	}
	protected function checkLogPath() {

	}
}
new cron_analytcsBooter();