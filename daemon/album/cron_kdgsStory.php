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
        $ignore_album_count = 0;
        $process_album_count = 0;

        while (true) {

            $limit = ($p - 1) * $per_page;
            $album_list = $album->get_list("`from`='kdgs' and `add_time` > '{$end_time}' order by `id` desc", "{$limit},{$per_page}");
            //$album_list = $album->get_list("`from`='kdgs' and `id`=12824");
            if (!$album_list) {
                break;
            }

            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_KDGS_STORY, "采集口袋故事  {$limit},{$per_page}");

            foreach ($album_list as $k => $v) {

                $process_album_count++;
                $story_list_count = 0;
                $ignore_story_count = 0;
                $add_story_count = 0;

                if (!$v['age_type'] || !empty($v['max_age'])) {
                    $v['age_type'] = $album->get_age_type($v['age_str']);
                    $age_str_arr = $album->get_age_arr($v['age_str']);
                    $v['min_age'] = $age_str_arr[0];
                    $v['max_age'] = $age_str_arr[1];
                    $album->update(array('age_type' => $v['age_type'],'min_age' => $v['min_age'],'max_age' => $v['max_age']), "`id`={$v['id']}");
                }

                // 获取口袋故事专辑列表
                $story_list_arr = $kdgs->get_album_story_list($v['link_url']);
                $story_list = $story_list_arr['items'];
                $story_list_count = count($story_list);
                $story_count = $story_list_arr['count'];

                // 判断专辑简介是否为空，若为空则读取故事专辑下第一个故事的简介
                if (empty($v['intro'])) {
                    $first_story_info = current($story_list);
                    if (!empty($first_story_info['intro'])) {
                        $album->update(array("intro" => $first_story_info['intro']), "`id` = '{$v['id']}'");
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_KDGS_STORY, "口袋专辑{$v['id']} 简介更新成功\r\n");
                    }
                }

                //如果两者数量不一致,很可能是收费内容.则不抓取故事内容,并将专辑设置为:下架,删除状态。但后台可见,以备参考
                if($story_count > $story_list_count || $story_list_count == 0) {
                    
                    $ignore_story_count = $ignore_story_count + $v['story_num'];
                    $ignore_album_count++;
                    $content = sprintf("[{$v['id']}]{$v['title']} 故事总数量:%d, 可获取 %d, 或为收费内容\r\n", $story_count, $story_list_count);
                    echo $content;
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_KDGS_STORY, $content);
                    $album->update(array('status' => 0,'online_status' => 0), "`id`={$v['id']}");
                    continue;

                }else{
                    $album->update(array('status' => 1), "`id`={$v['id']}");
                }

                // 如果故事数量没有更新 则不再查故事库
                if (count($story_list) == $v['story_num']) {

                    $ignore_story_count = $ignore_story_count + $v['story_num'];
                    $content = sprintf("[{$v['id']}]{$v['title']} 故事总数量:%d, 已忽略 %d, 新增 %d\r\n", $story_list_count, $ignore_story_count, $add_story_count);
                    echo $content;
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_KDGS_STORY, $content);
                    continue;
                }

                $vieworder = 0;
                foreach ($story_list as $k2 => $v2) {
                    // 默认故事的排序，按源页面故事排序
                    $vieworder++;

                    $exists = $story->check_exists("`album_id` = {$v['id']} and `source_audio_url`='{$v2['source_audio_url']}'");
                    if ($exists) {
                        $ignore_story_count++;
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
                        $add_story_count++;
                        //MnsQueueManager::pushAlbumToSearchQueue($story_id);
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_KDGS_STORY,"{$story_id} 入库");
                    } else {

                        $content = '没有写入成功' . var_export($v, true) . var_export($v2, true)."\r\n";
                        echo $content;
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_KDGS_STORY,$content);

                    }
                }
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_KDGS_STORY, "口袋故事专辑 {$v['id']} 新增 {$add_story_count}\r\n");
                $album->update_story_num($v['id']);

                $content = sprintf("[{$v['id']}]{$v['title']} 故事总数量:%d, 已忽略 %d, 新增 %d\r\n", $story_list_count, $ignore_story_count, $add_story_count);
                echo $content;
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_KDGS_STORY, $content);
            }

            $p++;
            sleep(3);
        }

        $content = sprintf("采集口袋故事结束 共处理专辑数量:%d,其中忽略专辑数量:%d\r\n",$process_album_count,$ignore_album_count);
        echo $content;
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, ManageCollectionCronLog::TYPE_KDGS_STORY, $content);
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