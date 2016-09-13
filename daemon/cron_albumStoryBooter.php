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
		        	'album/cron_uploadAudio.php -m 10 -r 1',
		        	'album/cron_uploadAudio.php -m 10 -r 2',
		        	'album/cron_uploadAudio.php -m 10 -r 3',
		        	'album/cron_uploadAudio.php -m 10 -r 4',
		        	'album/cron_uploadAudio.php -m 10 -r 5',
		        	'album/cron_uploadAudio.php -m 10 -r 6',
		        	'album/cron_uploadAudio.php -m 10 -r 7',
		        	'album/cron_uploadAudio.php -m 10 -r 8',
		        	'album/cron_uploadAudio.php -m 10 -r 9',
		        	'album/cron_uploadAudio.php -m 10 -r 10',
				);
		return $list;
	}
	protected function checkLogPath() {

	}
}
new cron_albumStoryBooter();