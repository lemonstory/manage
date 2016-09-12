<?php
/**
 * lrts故事分类采集
 */
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_lrtsStory extends DaemonBase {
    protected $processnum = 1;
    protected function deal() {
        $this->c_lrts_story();
        exit;
    }

    protected function checkLogPath() {}

    protected function c_lrts_story() {
        $album = new Album();
        $story = new Story();
        $lrts  = new Lrts();
        $story_url = new StoryUrl();
        $p = 1;
        $per_page = 500;
        //http://www.ximalaya.com/2987462/album/254575?page=1
        //这个专辑从14年至16年一直在更新
        //取添加时间为5年前添加至系统的专辑(连续更新一个专辑5年以上,是件很牛的事情)
        $end_time = date("Y-m-d H:i:s", time() - 86400 * 30 * 12 * 5);
        $manageCollectionCronLog = new ManageCollectionCronLog();
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, ManageCollectionCronLog::TYPE_LRTS_STORY, "采集懒人听书故事开始 添加时间大于 {{$end_time}}");

        while (true) {
            $limit = ($p - 1) * $per_page;
            $album_list = $album->get_list("`from`='lrts' and `add_time` > '{$end_time}' order by `id` desc", "{$limit},{$per_page}");
            //$album_list = $album->get_list("`id`=15098");
            if (!$album_list) {
                break;
            }
            $time = time();
            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_LRTS_STORY, "采集懒人听书故事  {$limit},{$per_page}");
            foreach($album_list as $k => $v) {

                $ignore_count = 0;
                $add_count = 0;

                // 获取懒人听书的专辑故事
                $album_story_info_list = $lrts->get_album_story_info_list($v['link_url']);
                $story_list_count = $album_story_info_list['album']['story_total_count'];
                $story_content_list = $album_story_info_list['story'];


                if (empty($story_content_list) || $story_list_count == 0) {
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_LRTS_STORY, "懒人听书专辑{$v['id']} 没有故事");
                    continue;
                }

                // 判断专辑简介是否为空，若为空则读取故事专辑下第一个故事的简介
                if (empty($v['intro'])) {
                    if (!empty($album_story_info_list['album']['intro'])) {
                        $album->update(array("intro" => $album_story_info_list['album']['intro']), "`id` = '{$v['id']}'");
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_LRTS_STORY, "懒人听书专辑{$v['id']} 简介更新成功");
                    }
                }

                // 如果故事的数量和专辑里面的故事数量相等则不再更新
                if ($story_list_count == $v['story_num']) {

                    $ignore_count = $ignore_count + $v['story_num'];
                    $content = sprintf("[{$v['title']}] 故事总数量:%d, 已忽略 %d, 新增 %d", $story_list_count, $ignore_count, $add_count);
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_LRTS_STORY, $content);
                    continue;
                }
                $vieworder = 0;
                foreach ($story_content_list as $k2 => $v2) {

                    $vieworder = $v2['view_order'];
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
                        'intro' => '',
                        'view_order' => $vieworder,
                        'times' => $v2['times'],
                        's_cover' => '',
                        'source_audio_url' => $v2['source_audio_url'],
                        'add_time' => date('Y-m-d H:i:s'),
                    ));

                    //lrts故事没有封面(和专辑封面相同)
                    if ($story_id) {
                        $add_count ++;
                        //MnsQueueManager::pushAlbumToSearchQueue($story_id);
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_LRTS_STORY,"{$story_id} 入库");
                    } else {

                        $content = '没有写入成功' . var_export($v, true) . var_export($v2, true);
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_LRTS_STORY,$content);
                    }
                }

                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_LRTS_STORY, "懒人听书专辑 {$v['id']} 新增 {$add_count}");
                //更新专辑内故事数量
                $album->update_story_num($v['id']);
            }
            $p++;
            sleep(1);
        }
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, ManageCollectionCronLog::TYPE_LRTS_STORY, "采集懒人听书故事结束");
    }
}
new cron_lrtsStory();