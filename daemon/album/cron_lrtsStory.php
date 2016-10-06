<?php
/**
 * lrts故事分类采集
 */
include_once(dirname(dirname(__FILE__)) . "/DaemonBase.php");

class cron_lrtsStory extends DaemonBase
{

    protected $processnum = 1;
    protected $circulation_process = true;
    protected $target_url = "";

    protected function deal()
    {

        $options = getopt("u:");
        if (!empty($options)) {
            $this->target_url = $options['u'];
        }
        $this->c_lrts_story();
        exit;
    }

    protected function checkLogPath()
    {

    }

    protected function c_lrts_story()
    {

        $album = new Album();
        $story = new Story();
        $lrts = new Lrts();
        $creator = new Creator();
        $p = 1;
        $per_page = 500;
        $limit = 0;

        //http://www.ximalaya.com/2987462/album/254575?page=1
        //这个专辑从14年至16年一直在更新
        //取添加时间为5年前添加至系统的专辑(连续更新一个专辑5年以上,是件很牛的事情)
        $end_time = date("Y-m-d H:i:s", time() - 86400 * 30 * 12 * 5);
        $manageCollectionCronLog = new ManageCollectionCronLog();
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, ManageCollectionCronLog::TYPE_LRTS_STORY, "采集懒人听书故事开始 添加时间大于 {{$end_time}}");

        while (true) {

            if ($this->circulation_process && empty($this->target_url)) {
                $limit = ($p - 1) * $per_page;
                $album_list = $album->get_list("`from`='lrts' and `add_time` > '{$end_time}' order by `id` asc", "{$limit},{$per_page}");
                //$album_list = $album->get_list("`id`=14807");
            } else {
                $album_list[] = array("link_url" => $this->target_url);
            }

            if (!$album_list) {
                break;
            }

            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_LRTS_STORY, "采集懒人听书故事  {$limit},{$per_page}");
            foreach ($album_list as $k => $album_item) {

                $creator_uid = null;
                $author_uid_arr = array();
                $translator_uid_arr = array();
                $illustrator_uid_arr = array();
                $anchor_uid_arr = array();
                $ignore_count = 0;
                $add_count = 0;

                // 获取懒人听书的专辑故事
                $album_story_info_list = $lrts->get_album_story_info_list($album_item['link_url']);
                //处理故事业务
                $story_list_count = $album_story_info_list['album']['story_total_count'];
                $story_content_list = $album_story_info_list['story'];


                if (empty($story_content_list) || $story_list_count == 0) {
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_LRTS_STORY, "懒人听书专辑{$album_item['id']} 没有故事");
                    continue;
                }

                // 判断专辑简介是否为空，若为空则读取故事专辑下第一个故事的简介
                if (empty($album_item['intro'])) {
                    if (!empty($album_story_info_list['album']['intro'])) {
                        $album->update(array("intro" => $album_story_info_list['album']['intro']), "`id` = '{$album_item['id']}'");
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_LRTS_STORY, "懒人听书专辑{$album_item['id']} 简介更新成功");
                    }
                }

                //处理创作者(主播,翻译,插画,主播)
                $album_author_str = $album_story_info_list['album']['author'];
                $album_anchor_str = $album_story_info_list['album']['anchor'];
                $content = sprintf("[%s]专辑[%s] 作者[%s] 主播[%s] \r\n", $album_item['id'], $album_item['title'], $album_author_str, $album_anchor_str);
                echo $content;


                if (!empty($album_author_str) || !empty($album_anchor_str)) {
                    $album_creator_arr = $lrts->get_album_creator($album_author_str, $album_anchor_str);
                    foreach ($album_creator_arr as $k => $album_creator_item) {

                        $name = $album_creator_item['name'];
                        $type = $album_creator_item['type'];
                        $album_creator_data = array();
                        $album_creator_data['is_author'] = 0;
                        $album_creator_data['is_translator'] = 0;
                        $album_creator_data['is_illustrator'] = 0;
                        $album_creator_data['is_anchor'] = 0;

                        switch ($type) {
                            case $lrts->AUTHOR:
                                $album_creator_data['is_author'] = 1;
                                break;
                            case $lrts->TRANSLATOR:
                                $album_creator_data['is_translator'] = 1;
                                break;
                            case $lrts->ILLUSTRATOR:
                                $album_creator_data['is_illustrator'] = 1;
                                break;
                            case $lrts->ANCHOR:
                                $album_creator_data['is_anchor'] = 1;
                                break;
                        }
                        $creator_uid = $creator->getCreatorUid($name);
                        if (empty($creator_uid)) {
//                            echo "haha";
//                            exit;
                            $creator_uid = $creator->addCreator($name, "", "", $album_creator_data['is_author'], $album_creator_data['is_translator'], $album_creator_data['is_illustrator'], $album_creator_data['is_anchor']);
                            $content = sprintf("新增创作者[%s] : uid = %d \r\n", $name, $album_creator_data);
                            echo $content;

                        } else {
                            $where = "uid = {$creator_uid}";
                            $ret = $creator->update($album_creator_data, $where);
                            $content = sprintf("修复创作者[%s] : uid = %d,is_author = %d, is_translator =  %d, is_illustrator =  %d, is_anchor = %d, ret = %d\r\n", $name, $creator_uid, $album_creator_data['is_author'], $album_creator_data['is_translator'], $album_creator_data['is_illustrator'], $album_creator_data['is_anchor'], $ret);
                            echo $content;
                        }
                        switch ($type) {
                            case $lrts->AUTHOR:
                                $author_uid_arr[] = $creator_uid;
                                break;
                            case $lrts->TRANSLATOR:
                                $translator_uid_arr[] = $creator_uid;
                                break;
                            case $lrts->ILLUSTRATOR:
                                $illustrator_uid_arr[] = $creator_uid;
                                break;
                            case $lrts->ANCHOR:
                                $anchor_uid_arr[] = $creator_uid;
                                break;
                        }
                    }
                }
                $data = array();
                if (!empty($author_uid_arr)) {
                    $data['author_uid'] = implode(",", $author_uid_arr);
                }
                if (!empty($translator_uid_arr)) {
                    $data['translator_uid'] = implode(",", $translator_uid_arr);
                }
                if (!empty($illustrator_uid_arr)) {
                    $data['illustrator_uid'] = implode(",", $illustrator_uid_arr);
                }
                if (!empty($anchor_uid_arr)) {
                    $data['anchor_uid'] = implode(",", $anchor_uid_arr);
                }

                // 如果故事的数量和专辑里面的故事数量相等则不再更新
                if ($story_list_count == $album_item['story_num']) {
                    $story_item_list = $story->get_filed_list("id,author_uid,translator_uid,illustrator_uid,anchor_uid", "`album_id` = {$album_item['id']}");
                    //检查作者信息
                    if (is_array($story_item_list) && !empty($story_item_list) && !empty($data)) {
                        foreach ($story_item_list as $k => $story_item) {
//                            if (empty($story_item['author_id'])) {
                            $story_id = $story_item['id'];
                            $where = "`id` = {$story_id}";
                            $ret = $story->update($data, $where);
                            $content = sprintf("[%d]故事整体为最新,只更新作者[%s] %d\r\n", $story_id, var_export($data, true), $ret);
                            echo $content;
//                            }
                        }
                    }

                    $ignore_count = $ignore_count + $album_item['story_num'];
                    $content = sprintf("[{$album_item['title']}] 故事总数量:%d, 已忽略 %d, 新增 %d", $story_list_count, $ignore_count, $add_count);
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_LRTS_STORY, $content);
                    continue;
                }
                $vieworder = 0;
                foreach ($story_content_list as $k2 => $story_item) {

                    //检查故事是否已存在
                    $vieworder = $story_item['view_order'];
                    $story_item_list = $story->get_filed_list("id,author_uid,translator_uid,illustrator_uid,anchor_uid", "`album_id` = {$album_item['id']} and `source_audio_url`='{$story_item['source_audio_url']}'", "", 1);
                    //检查作者信息
                    if (is_array($story_item_list) && !empty($story_item_list) && !empty($data)) {
                        foreach ($story_item_list as $k => $story_item) {
//                            if(empty($story_item['author_id'])) {
                            $story_id = $story_item['id'];
                            $where = "`id` = {$story_id}";
                            $ret = $story->update($data, $where);
                            $content = sprintf("[%d]故事已存在,只更新作者[%s] %d\r\n", $story_id, var_export($data, true), $ret);
                            echo $content;
//                            }
                        }
                        $ignore_count++;
                        continue;
                    }
                    if (empty($vieworder)) {
                        $vieworder = $k2;
                    }

                    $story_id = $story->insert(array(
                        'album_id' => $album_item['id'],
                        'title' => addslashes($story_item['title']),
                        'intro' => '',
                        'view_order' => $vieworder,
                        'times' => $story_item['times'],
                        's_cover' => '',
                        'source_audio_url' => $story_item['source_audio_url'],
                        'author_uid' => isset($data['author_uid']) ? $data['author_uid'] : null,
                        'translator_uid' => isset($data['translator_uid']) ? $data['translator_uid'] : null,
                        'illustrator_uid' => isset($data['illustrator_uid']) ? $data['illustrator_uid'] : null,
                        'anchor_uid' => isset($data['anchor_uid']) ? $data['anchor_uid'] : null,
                        'add_time' => date('Y-m-d H:i:s'),
                    ));

                    //lrts故事没有封面(和专辑封面相同)
                    if ($story_id) {
                        $add_count++;
                        //MnsQueueManager::pushAlbumToSearchQueue($story_id);
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_LRTS_STORY, "{$story_id} 入库");
                    } else {

                        $content = '没有写入成功' . var_export($album_item, true) . var_export($story_item, true);
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_LRTS_STORY, $content);
                    }
                }

                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_LRTS_STORY, "懒人听书专辑 {$album_item['id']} 新增 {$add_count}");
                //更新专辑内故事数量
                $album->update_story_num($album_item['id']);
            }
            $p++;
            sleep(1);
            //exit;
        }
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, ManageCollectionCronLog::TYPE_LRTS_STORY, "采集懒人听书故事结束");
    }
}

new cron_lrtsStory();