<?php
/**
 * 故事采集
 */
include_once(dirname(dirname(__FILE__)) . "/DaemonBase.php");

class cron_bevaStory extends DaemonBase
{
    protected $processnum = 1;

    protected function deal()
    {
        $this->c_beva_story();
        exit;
    }

    protected function checkLogPath()
    {
    }


    protected function c_beva_story()
    {
        $p = 1;
        $per_page = 500;
        //http://www.ximalaya.com/2987462/album/254575?page=1
        //这个专辑从14年至16年一直在更新
        //取添加时间为5年前添加至系统的专辑(连续更新一个专辑5年以上,是件很牛的事情)
        $end_time = date("Y-m-d H:i:s", time() - 86400 * 30 * 12 * 5);

        $albumObj = new Album();
        $storyObj = new Story();
        $bevaObj = new Beva();
        $storyUrl = new StoryUrl();
        $manageCollectionCronLog = new ManageCollectionCronLog();
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, ManageCollectionCronLog::TYPE_BEVA_STORY, "采集开始 添加时间大于 {{$end_time}}");
        $ignore_album_count = 0;
        $process_album_count = 0;

        while (true) {

            $limit = ($p - 1) * $per_page;
            $album_list = $albumObj->get_list("`from`='beva' and `add_time` > '{$end_time}' order by `id` desc", "{$limit},{$per_page}");
            //$album_list = $albumObj->get_list("`from`='beva' and `id`=15386");
            if (!$album_list) {
                break;
            }

            //$manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_BEVA_STORY, "采集  {$limit},{$per_page}");

            foreach ($album_list as $k => $album) {

                $process_album_count++;
                $story_list_count = 0;
                $ignore_story_count = 0;
                $add_story_count = 0;

                // 获取故事列表
                $story_list_arr = $bevaObj->get_album_story_list($album['link_url']);
                $story_list = $story_list_arr['story'];
                $story_list_count = count($story_list);

                // 判断专辑简介是否为空，若为空则读取故事专辑下第一个故事的简介
                if (empty($album['intro'])) {
                    if (!empty($story_list_arr['album']['intro'])) {
                        $albumObj->update(array("intro" => $story_list_arr['album']['intro']), "`id` = '{$album['id']}'");
                        echo "专辑{$album['id']} 简介更新成功\r\n";
                    }
                }
                // 如果故事数量没有更新 则不再查故事库
                if ($story_list_count == $album['story_num']) {

                    $ignore_story_count = $ignore_story_count + $album['story_num'];
                    $content = sprintf("[{$album['id']}]{$album['title']} 故事总数量:%d, 已忽略 %d, 新增 %d\r\n", $story_list_count, $ignore_story_count, $add_story_count);
                    echo $content;
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_BEVA_STORY, $content);
                    continue;
                }

                $album_listen_num = 0;
                foreach ($story_list as $key => $story) {

                    $exists = $storyObj->check_exists("`source_audio_url`='{$story['source_audio_url']}'");
                    if ($exists) {

                        $ignore_story_count++;
                        continue;
                    } else {

                        $story_id = $storyObj->insert(array(
                            'album_id' => $album['id'],
                            'title' => addslashes($story['title']),
                            'view_order' => $story['view_order'],
                            'source_audio_url' => $story['source_audio_url'],
                            'add_time' => date('Y-m-d H:i:s'),
                        ));

                        if ($story_id) {

                            $album_listen_num = $album_listen_num + $story['listen_num'];
                            $add_story_count++;
                            //MnsQueueManager::pushAlbumToSearchQueue($story_id);
                            $content = "{$story_id} 入库 \r\n";
                            echo $content;
                            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_BEVA_STORY, $content);

                        } else {

                            $content = '没有写入成功' . var_export($album, true) . var_export($story, true) . "\r\n";
                            echo $content;
                            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_BEVA_STORY, $content);

                        }
                    }
                }
                
                //更新专辑收听数量
                $listenObj = new Listen();
                $albumListenArr = $listenObj->getAlbumListenNum($album['id']);
                if(empty($albumListenArr[$album['id']]['num']) && $album_listen_num > 0) {
                    $listenObj->addAlbumListenCountDb($album['id'],$album_listen_num);
                }

                //更新专辑声音数量
                $albumObj->update_story_num($album['id']);

                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_BEVA_STORY, "beva专辑 {$album['id']} 新增 {$add_story_count}\r\n");
                $content = sprintf("[{$album['id']}]{$album['title']} 故事总数量:%d, 已忽略 %d, 新增 %d\r\n", $story_list_count, $ignore_story_count, $add_story_count);
                echo $content;
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_BEVA_STORY, $content);
            }

            $p++;
            sleep(3);
        }

        $content = sprintf("故事结束 共处理专辑数量:%d,其中忽略专辑数量:%d\r\n", $process_album_count, $ignore_album_count);
        echo $content;
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, ManageCollectionCronLog::TYPE_BEVA_STORY, $content);
    }

    protected function writeLog($content = '')
    {
        static $manageCollectionCronLog = null;
        if (!$manageCollectionCronLog) {
            $manageCollectionCronLog = new ManageCollectionCronLog();
        }
        $manageCollectionCronLog->insert(array('type' => 'beva_story', 'content' => $content));

    }

}

new cron_bevaStory();