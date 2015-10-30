<?php
/**
 * 口袋故事故事采集
 */
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_kdgsStory extends DaemonBase {
    protected $processnum = 1;
	protected function deal() {
		$this->c_kdgs_story();
	    exit;
	}

	protected function checkLogPath() {}


	protected function c_kdgs_story() {
        $album = new Album();
        $story = new Story();
        $kdgs  = new Kdgs();
        $story_url = new StoryUrl();
        $this->writeLog("采集口袋故事开始");
        $p = 1;
        $per_page = 500;

        while (true) {
            $limit = ($p - 1) * $per_page;
            $album_list = $album->get_list("`from`='kdgs'", " {$limit},{$per_page}");
            if (!$album_list) {
                break;
            }

            $this->writeLog("采集口袋故事  {$limit},{$per_page}");

            foreach ($album_list as $k => $v) {
                if (!$v['age_type']) {
                    $v['age_type'] = $album->get_age_type($v['age_str']);
                    $album->update(array('age_type' => $v['age_type']), "`id`={$v['id']}");
                }
                $story_list = $kdgs->get_album_story_list($v['link_url']);
                foreach($story_list as $k2 => $v2) {
                    $exists = $story->check_exists("`source_audio_url`='{$v2['source_audio_url']}'");
                    if ($exists) {
                        continue;
                    }
                    $story_id = $story->insert(array(
                        'album_id' => $v['id'],
                        'title' => addslashes($v2['title']),
                        'intro' => addslashes($v2['intro']),
                        's_cover' => $v2['cover'],
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
                        'source_url'       => $v2['cover'],
                        'source_file_name' => ltrim(strrchr($v2['cover'], '/'), '/'),
                        'add_time'         => date('Y-m-d H:i:s'),
                    ));
                    if ($story_id) {
                        $this->writeLog("{$story_id} 入库");
                    } else {
                        $this->writeLog('没有写入成功'.var_export($v, true).var_export($v2, true));
                    }
                }
            }

            $p++;
        }
        $this->writeLog("采集口袋故事结束");
    }

    protected function writeLog($content = '')
    {
        static $manageCollectionCronLog = null;
        if (!$manageCollectionCronLog) {
            $manageCollectionCronLog = new ManageCollectionCronLog();
        }
        $manageCollectionCronLog->insert(array('type' => 'kdgs_story', 'content' => $content));
        
    }

}
new cron_kdgsStory();