<?php
//抓取所有(或某一个)专辑的故事
//
//业务逻辑:
//      根据输入参考u(可选)抓取专辑信息
//      a -album_id(例如:15148) 存在时抓取该专辑下的故事,不存在时抓取所有故事(依赖于album)
//使用:
//  /alidata/server/php/bin/php /alidata1/www/htdocs/manage.xiaoningmeng.net/daemon/album/cron_xmlyStory.php -a15148
//
include_once(dirname(dirname(__FILE__)) . "/DaemonBase.php");

class cron_xmlyStory extends DaemonBase
{
    protected $processnum = 1;

    protected function deal()
    {
        $options = getopt("a::");
        if (!empty($options)) {

            $album_id = $options['a'];
        }
        $album_id = intval($album_id);
        $this->c_xmly_story($album_id);
        exit;
    }

    protected function checkLogPath()
    {

    }

    protected function c_xmly_story($album_id)
    {
        $album = new Album();
        $manageCollectionCronLog = new ManageCollectionCronLog();
        if (!empty($album_id)) {

            $content = "采集喜马拉雅故事开始 专辑id = {$album_id}\r\n";
            echo $content;
            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, ManageCollectionCronLog::TYPE_XMLY_STORY, $content);
            $album_info = $album->get_album_info($album_id);
            $this->fetch_story($album_info);

        } else {
            $p = 1;
            $per_page = 500;
            //http://www.ximalaya.com/2987462/album/254575?page=1
            //这个专辑从14年至16年一直在更新
            //取添加时间为5年前添加至系统的专辑(连续更新一个专辑5年以上,是件很牛的事情)
            $end_time = date("Y-m-d H:i:s", time() - 86400 * 30 * 12 * 5);
            $content = "采集喜马拉雅故事开始 添加时间大于 {{$end_time}}\r\n";
            echo $content;
            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, ManageCollectionCronLog::TYPE_XMLY_STORY, $content);

            while (true) {
                $limit = ($p - 1) * $per_page;
                $album_list = $album->get_list("`from`='xmly' and `add_time` > '{$end_time}' order by `id` desc", "{$limit},{$per_page}");
                //$album_list = $album->get_list("`id`=13747");
                if (!$album_list) {
                    break;
                }
                $time = time();
                $content = "采集喜马拉雅故事  {$limit},{$per_page}\r\n";
                echo $content;
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_STORY, $content);
                foreach ($album_list as $k => $v) {
                    $this->fetch_story($v);
                }
                $p++;
                sleep(1);
            }
        }
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, ManageCollectionCronLog::TYPE_XMLY_STORY, "采集喜马拉雅故事结束");
    }

    protected function fetch_story($album_info)
    {
        $album = new Album();
        $story = new Story();
        $xmly = new Xmly();
        $story_url = new StoryUrl();
        $manageCollectionCronLog = new ManageCollectionCronLog();

        $story_list_count = 0;
        $ignore_count = 0;
        $add_count = 0;
        $xmly_album_id = Http::sub_data($album_info['link_url'], 'album/');

        // 获取喜马拉雅的专辑故事
        $story_url_list = $xmly->get_story_url_list($xmly_album_id);
        $story_list_count = count($story_url_list);
        if (empty($story_url_list)) {
            $content = "喜马拉雅专辑{$album_info['id']} 没有故事\r\n";
            echo $content;
            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_STORY, $content);

        } else {
            // 判断专辑简介是否为空，若为空则读取故事专辑下第一个故事的简介
            if (empty($album_info['intro'])) {
                $first_story_url = current($story_url_list);
                $first_story_info = $xmly->get_story_info($first_story_url);
                if (!empty($first_story_info['intro'])) {
                    $album->update(array("intro" => $first_story_info['intro']), "`id` = '{$album_info['id']}'");
                    $content = "喜马拉雅专辑{$album_info['id']} 简介更新成功\r\n";
                    echo $content;
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_STORY, $content);
                }
            }

            // 如果故事的数量和专辑里面的故事数量相等则不再更新
            if (count($story_url_list) == $album_info['story_num']) {

                $ignore_count = $ignore_count + $album_info['story_num'];
                $content = sprintf("[{$album_info['title']}] 故事总数量:%d, 已忽略 %d, 新增 %d\r\n", $story_list_count, $ignore_count, $add_count);
                echo $content;
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_STORY, $content);

            } else {
                $vieworder = 0;
                foreach ($story_url_list as $k2 => $v2) {
                    // 默认故事的排序，按源页面故事排序
                    $vieworder++;

                    $v2 = $xmly->get_story_info($v2);
                    if (!$v2) {
                        continue;
                    }
                    $source_audio_path_arr = parse_url($v2['source_audio_url']);
                    //cdn根据域名做了切分
                    $exists = $story->check_exists("`album_id` = {$album_info['id']} and LOCATE('{$source_audio_path_arr['path']}',`source_audio_url`) > 0");
                    if ($exists) {
                        $ignore_count++;
                        continue;
                    }
                    if (empty($vieworder)) {
                        $vieworder = $k2;
                    }

                    $story_id = $story->insert(array(
                        'album_id' => $album_info['id'],
                        'title' => addslashes($v2['title']),
                        'intro' => addslashes($v2['intro']),
                        'view_order' => $vieworder,
                        's_cover' => $v2['s_cover'],
                        'source_audio_url' => $v2['source_audio_url'],
                        'add_time' => date('Y-m-d H:i:s'),
                    ));
                    $story_url->insert(array(
                        'res_name' => 'story',
                        'res_id' => $story_id,
                        'field_name' => 'cover',
                        'source_url' => $v2['s_cover'],
                        'source_file_name' => ltrim(strrchr($v2['s_cover'], '/'), '/'),
                        'add_time' => date('Y-m-d H:i:s'),
                    ));
                    if ($story_id) {
                        $add_count++;
                        //MnsQueueManager::pushAlbumToSearchQueue($story_id);
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_XMLY_STORY, "{$story_id} 入库");
                    } else {

                        $content = '没有写入成功' . var_export($album_info, true) . var_export($v2, true);
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_XMLY_STORY, $content);
                    }
                }
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_STORY, "喜马拉雅专辑 {$album_info['id']} 新增 {$add_count}");
                $album->update_story_num($album_info['id']);
            }
        }
    }
}

new cron_xmlyStory();