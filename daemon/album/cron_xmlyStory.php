<?php
/**
 * 喜马拉雅故事分类采集
 */
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_xmlyStory extends DaemonBase {
    protected $processnum = 1;
	protected function deal() {
		$this->c_xmly_story();
	    exit;
	}

	protected function checkLogPath() {}

	protected function c_xmly_story() {
        $album = new Album();
        $story = new Story();
        $xmly  = new Xmly();
        $story_url = new StoryUrl();
        $this->writeLog("采集喜马拉雅故事开始");
        $p = 1;
        $per_page = 500;

        while (true) {
            $limit = ($p - 1) * $per_page;
            $album_list = $album->get_list("`from`='xmly'", " {$limit},{$per_page}");
            if (!$album_list) {
                break;
            }
            $time = time();
            $this->writeLog("采集喜马拉雅故事  {$limit},{$per_page}");
            foreach($album_list as $k => $v) {
	        	$album_id = Http::sub_data($v['link_url'], 'album/');
	        	$page = 1;
	        	$story_list = $xmly->get_story_list($album_id, $page);

	        	while (true) {
	        		$story_list = $xmly->get_story_list($album_id, $page);
	        		if (!$story_list) {
	        			break;
	        		}

	                foreach ($story_list as $k2 => $v2) {
	                	$exists = $story->check_exists("`source_audio_url`='{$v2['source_audio_url']}'");
		                if ($exists) {
		                    continue;
		                }
		                $story_id = $story->insert(array(
		                    'album_id' => $v['id'],
		                    'title' => addslashes($v2['title']),
		                    'intro' => addslashes($v2['intro']),
		                    's_cover' => $v2['s_cover'],
		                    'source_audio_url' => $v2['source_audio_url'],
		                    'add_time' => date('Y-m-d H:i:s'),
		                ));
		                if ($story_id) {
		                    MnsQueueManager::pushAlbumToSearchQueue($story_id);
		                }
		                $story_url->insert(array(
		                    'res_name'         => 'story',
		                    'res_id'           => $story_id,
		                    'field_name'       => 'cover',
		                    'source_url'       => $v2['s_cover'],
		                    'source_file_name' => ltrim(strrchr($v2['s_cover'], '/'), '/'),
		                    'add_time'         => date('Y-m-d H:i:s'),
		                ));
		                if ($story_id) {
	                        $this->writeLog("{$story_id} 入库");
	                    } else {
	                        $this->writeLog('没有写入成功'.var_export($v, true).var_export($v2, true));
	                    }
	                }
	                $page ++;
	        	}
	        }
            $p++;
        }
        $this->writeLog("采集喜马拉雅故事结束");
    }

    protected function writeLog($content = '')
    {
        static $manageCollectionCronLog = null;
        if (!$manageCollectionCronLog) {
            $manageCollectionCronLog = new ManageCollectionCronLog();
        }
        $manageCollectionCronLog->insert(array('type' => 'xmly_story', 'content' => $content));
        
    }

}
new cron_xmlyStory();