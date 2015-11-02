<?php
/**
 * 口袋故事故事采集
 */
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_kdgsStory extends DaemonBase {
    protected $processnum = 1;
	protected function deal() {
		$this->push_album();
	    exit;
	}

	protected function checkLogPath() {}


	protected function push_album() {
		if (isset($_SERVER['argv'][1])) {
			$page = $_SERVER['argv'][1];
		} else {
			exit('no page params');
		}
		if (isset($_SERVER['argv'][2])) {
			$perpage = $_SERVER['argv'][2];
		} else {
			$perpage = 1000;
		}
		$limit = ($page - 1) * $perpage;
		$story = new Story();
		$story_list = $story->get_list("`id`>685 and `id` <= 50000", "{$limit}, {$perpage}", '', "ORDER BY id ASC");
		foreach ($story_list as $k => $v) {
			MnsQueueManager::pushAlbumToSearchQueue($v['id']);
		}
		$this->writeLog("{$limit}, {$perpage}");
    }

    protected function writeLog($content = '')
    {
        static $manageCollectionCronLog = null;
        if (!$manageCollectionCronLog) {
            $manageCollectionCronLog = new ManageCollectionCronLog();
        }
        $manageCollectionCronLog->insert(array('type' => 'push_album', 'content' => $content));
        
    }

}
new cron_kdgsStory();