<?php
/**
 * 喜马拉雅故事分类采集
 */
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_fixXmlyStoryCover extends DaemonBase {
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
        $lastmonth = date("Y-m-d H:i:s", time() - 86400 * 30);

        while (true) {
            $limit = ($p - 1) * $per_page;
            $album_list = $album->get_list("`from`='xmly' and `add_time` > '{$lastmonth}' order by `id` desc", "{$limit},{$per_page}");
            if (!$album_list) {
                break;
            }
            $time = time();
            $this->writeLog("采集喜马拉雅故事  {$limit},{$per_page}");
            foreach($album_list as $k => $v) {
	        	$xmly_album_id = Http::sub_data($v['link_url'], 'album/');

	        	// 获取喜马拉雅的专辑故事
        		$story_url_list = $xmly->get_story_url_list($xmly_album_id);

	        	$update_num = 0;
	        	foreach ($story_url_list as $k2 => $v2) {
	        		$v2 = $xmly->get_story_info($v2);
	        		if (!$v2) {
	        			continue;
	        		}
                	$story_info = $story->get_filed("`id` > 256132 and `album_id` = {$v['id']} and `source_audio_url`='{$v2['source_audio_url']}'");
                	var_dump($story_info);exit;
                }
                $this->writeLog("喜马拉雅专辑 {$v['id']} 新增 {$update_num}");
                $album->update_story_num($v['id']);
	        }
            $p++;
			sleep(3);
        }
        $this->writeLog("采集喜马拉雅故事结束");
    }

    protected function writeLog($content = '')
    {
    	echo $content;
    	echo "\n";
    	return true;
    }

}
new cron_fixXmlyStoryCover();