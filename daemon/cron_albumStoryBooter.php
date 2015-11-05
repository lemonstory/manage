<?php
/*
 * 专辑 故事 上传相关操作
 */
include 'DaemonBase.php';
class cron_albumStoryBooter extends DaemonBase {
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
		        	'album/cron_uploadAudioOdd.php',
		        	'cron_uploadAudioEven.php'
				);
		return $list;
	}
	protected function checkLogPath() {

	}
}
new cron_albumStoryBooter();