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
        $p = 1;
        $per_page = 500;
		//http://www.ximalaya.com/2987462/album/254575?page=1
		//这个专辑从14年至16年一直在更新
		//取添加时间为5年前添加至系统的专辑(连续更新一个专辑5年以上,是件很牛的事情)
		$end_time = date("Y-m-d H:i:s", time() - 86400 * 30 * 12 * 5);
		$manageCollectionCronLog = new ManageCollectionCronLog();
		$manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, ManageCollectionCronLog::TYPE_XMLY_STORY, "采集喜马拉雅故事开始 添加时间大于 {{$end_time}}");

        while (true) {
            $limit = ($p - 1) * $per_page;
            $album_list = $album->get_list("`from`='xmly' and `add_time` > '{$end_time}' order by `id` desc", "{$limit},{$per_page}");
            //$album_list = $album->get_list("`id`=13747");
            if (!$album_list) {
                break;
            }
            $time = time();
            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_STORY, "采集喜马拉雅故事  {$limit},{$per_page}");
            foreach($album_list as $k => $v) {

				$story_list_count = 0;
				$ignore_count = 0;
				$add_count = 0;
	        	$xmly_album_id = Http::sub_data($v['link_url'], 'album/');

	        	// 获取喜马拉雅的专辑故事
        		$story_url_list = $xmly->get_story_url_list($xmly_album_id);

				$story_list_count = count($story_url_list);
        		if (empty($story_url_list)) {
					$manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_STORY, "喜马拉雅专辑{$v['id']} 没有故事");
        		    continue;
        		}
        		
        		// 判断专辑简介是否为空，若为空则读取故事专辑下第一个故事的简介
        		if (empty($v['intro'])) {
        		    $first_story_url = current($story_url_list);
        		    $first_story_info = $xmly->get_story_info($first_story_url);
        		    if (!empty($first_story_info['intro'])) {
        		        $album->update(array("intro" => $first_story_info['intro']), "`id` = '{$v['id']}'");
						$manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_STORY, "喜马拉雅专辑{$v['id']} 简介更新成功");
        		    }
        		}
        		
	        	// 如果故事的数量和专辑里面的故事数量相等则不再更新
	        	if (count($story_url_list) == $v['story_num']) {

					$ignore_count = $ignore_count + $v['story_num'];
					$content = sprintf("[{$v['title']}] 故事总数量:%d, 已忽略 %d, 新增 %d", $story_list_count, $ignore_count, $add_count);
					$manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_STORY, $content);
	        		continue;
	        	}
	        	$vieworder = 0;
	        	foreach ($story_url_list as $k2 => $v2) {
	        	    // 默认故事的排序，按源页面故事排序
	        	    $vieworder++;
	        	    
	        		$v2 = $xmly->get_story_info($v2);
	        		if (!$v2) {
	        			continue;
	        		}
                	$exists = $story->check_exists("`album_id` = {$v['id']} and `source_audio_url`='{$v2['source_audio_url']}'");
	                if ($exists) {
						$ignore_count++;
	                    continue;
	                }
	                if (empty($vieworder)) {
	                    $vieworder = $k2;
	                }
	                
	                $story_id = $story->insert(array(
	                    'album_id' => $v['id'],
	                    'title' => addslashes($v2['title']),
	                    'intro' => addslashes($v2['intro']),
                        'view_order' => $vieworder,
	                    's_cover' => $v2['s_cover'],
						'source_audio_url' => $v2['source_audio_url'],
	                    'add_time' => date('Y-m-d H:i:s'),
	                ));
	                $story_url->insert(array(
	                    'res_name'         => 'story',
	                    'res_id'           => $story_id,
	                    'field_name'       => 'cover',
	                    'source_url'       => $v2['s_cover'],
	                    'source_file_name' => ltrim(strrchr($v2['s_cover'], '/'), '/'),
	                    'add_time'         => date('Y-m-d H:i:s'),
	                ));
	                if ($story_id) {
						$add_count ++;
	                	//MnsQueueManager::pushAlbumToSearchQueue($story_id);
						$manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_XMLY_STORY,"{$story_id} 入库");
                    } else {

						$content = '没有写入成功' . var_export($v, true) . var_export($v2, true);
						$manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_XMLY_STORY,$content);
                    }
                }

				$manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_STORY, "喜马拉雅专辑 {$v['id']} 新增 {$add_count}");
                $album->update_story_num($v['id']);
	        }
            $p++;
			sleep(1);
        }
 		$manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, ManageCollectionCronLog::TYPE_XMLY_STORY, "采集喜马拉雅故事结束");
    }
}
new cron_xmlyStory();