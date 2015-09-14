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
		$list = array();
		return $list;
	}
	protected function checkLogPath() {

	}
}
new cron_analytcsBooter();