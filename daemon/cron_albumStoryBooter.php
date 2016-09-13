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
		        	'album/cron_uploadAudio.php -m 5 -r 1',
		        	'album/cron_uploadAudio.php -m 5 -r 2',
		        	'album/cron_uploadAudio.php -m 5 -r 3',
		        	'album/cron_uploadAudio.php -m 5 -r 4',
		        	'album/cron_uploadAudio.php -m 5 -r 5',
				);
		return $list;
	}
	protected function checkLogPath() {

	}
}
new cron_albumStoryBooter();