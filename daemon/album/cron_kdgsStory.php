<?php
/**
 * 口袋故事故事采集
 */
include_once(dirname(dirname(__FILE__)) . "/DaemonBase.php");

class cron_kdgsStory extends DaemonBase
{
    protected $processnum = 1;

    protected function deal()
    {
        $this->c_kdgs_story();
        exit;
    }

    protected function checkLogPath()
    {
    }


    protected function c_kdgs_story()
    {
        $p = 1;
        $per_page = 500;
        //http://www.ximalaya.com/2987462/album/254575?page=1
        //这个专辑从14年至16年一直在更新
        //取添加时间为5年前添加至系统的专辑(连续更新一个专辑5年以上,是件很牛的事情)
        $end_time = date("Y-m-d H:i:s", time() - 86400 * 30 * 12 * 5);

        $album = new Album();
        $story = new Story();
        $kdgs = new Kdgs();
        $story_url = new StoryUrl();
        $manageCollectionCronLog = new ManageCollectionCronLog();
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, ManageCollectionCronLog::TYPE_KDGS_STORY, "采集口袋故事开始 添加时间大于 {{$end_time}}");

        while (true) {

            $limit = ($p - 1) * $per_page;
            $album_list = $album->get_list("`from`='kdgs' and `add_time` > '{$end_time}' order by `id` desc", "{$limit},{$per_page}");
            if (!$album_list) {
                break;
            }

            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_KDGS_STORY, "采集口袋故事  {$limit},{$per_page}");

            foreach ($album_list as $k => $v) {

                $story_list_count = 0;
                $ignore_count = 0;
                $add_count = 0;

                if (!$v['age_type'] || !empty($v['max_age'])) {
                    $v['age_type'] = $album->get_age_type($v['age_str']);
                    $age_str_arr = $album->get_age_arr($v['age_str']);
                    $v['min_age'] = $age_str_arr[0];
                    $v['max_age'] = $age_str_arr[1];
                    $album->update(array('age_type' => $v['age_type'],'min_age' => $v['min_age'],'max_age' => $v['max_age']), "`id`={$v['id']}");
                }

                // 获取口袋故事专辑列表
                $story_list = $kdgs->get_album_story_list($v['link_url']);
                $story_list_count = count($story_list);

                // 判断专辑简介是否为空，若为空则读取故事专辑下第一个故事的简介
                if (empty($v['intro'])) {
                    $first_story_info = current($story_list);
                    if (!empty($first_story_info['intro'])) {
                        $album->update(array("intro" => $first_story_info['intro']), "`id` = '{$v['id']}'");
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_KDGS_STORY, "口袋专辑{$v['id']} 简介更新成功");
                    }
                }

                // 如果故事数量没有更新 则不再查故事库
                if (count($story_list) == $v['story_num']) {

                    $ignore_count = $ignore_count + $v['story_num'];
                    $content = sprintf("[{$v['title']}] 故事总数量:%d, 已忽略 %d, 新增 %d", $story_list_count, $ignore_count, $add_count);
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_KDGS_STORY, $content);
                    continue;
                }

                $vieworder = 0;
                foreach ($story_list as $k2 => $v2) {
                    // 默认故事的排序，按源页面故事排序
                    $vieworder++;

                    $exists = $story->check_exists("`album_id` = {$v['id']} and `source_audio_url`='{$v2['source_audio_url']}'");
                    if ($exists) {
                        $ignore_count++;
                        continue;
                    }
                    if (empty($vieworder)) {
                        $vieworder = 10000;
                    }

                    $story_id = $story->insert(array(
                        'album_id' => $v['id'],
                        'title' => addslashes($v2['title']),
                        'intro' => addslashes($v2['intro']),
                        'view_order' => $vieworder,
                        's_cover' => $v2['cover'],
                        'source_audio_url' => $v2['source_audio_url'],
                        'add_time' => date('Y-m-d H:i:s'),
                    ));
                    $story_url->insert(array(
                        'res_name' => 'story',
                        'res_id' => $story_id,
                        'field_name' => 'cover',
                        'source_url' => $v2['cover'],
                        'source_file_name' => ltrim(strrchr($v2['cover'], '/'), '/'),
                        'add_time' => date('Y-m-d H:i:s'),
                    ));
                    if ($story_id) {
                        $add_count++;
                        //MnsQueueManager::pushAlbumToSearchQueue($story_id);
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_KDGS_STORY,"{$story_id} 入库");
                    } else {

                        $content = '没有写入成功' . var_export($v, true) . var_export($v2, true);
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_KDGS_STORY,$content);

                    }
                }
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_KDGS_STORY, "口袋故事专辑 {$v['id']} 新增 {$add_count}");
                $album->update_story_num($v['id']);

                $content = sprintf("[{$v['title']}] 故事总数量:%d, 已忽略 %d, 新增 %d", $story_list_count, $ignore_count, $add_count);
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_KDGS_STORY, $content);
            }

            $p++;
            sleep(3);
        }
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, ManageCollectionCronLog::TYPE_KDGS_STORY, "采集口袋故事结束");
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