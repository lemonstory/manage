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
        $album_id = 0;
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
                //$album_list = $album->get_list("`id`=10712");
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
                //exit;
                
            }
        }
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, ManageCollectionCronLog::TYPE_XMLY_STORY, "采集喜马拉雅故事结束");
    }

    protected function fetch_story($album_info)
    {
        $albumObj = new Album();
        $storyObj = new Story();
        $xmlyObj = new Xmly();
        $storyUrlObj = new StoryUrl();
        $manageCollectionCronLog = new ManageCollectionCronLog();
        $creatorObj = new Creator();
        $userObj = new User();

        $story_list_count = 0;
        $ignore_count = 0;
        $add_count = 0;
        
        $xmly_album_info = $xmlyObj->get_album_info($album_info['link_url']);
        //更新主播信息
        if(!empty($xmly_album_info['anchor']['name'])) {

            $name = $xmly_album_info['anchor']['name'];
            $creator_uid = $creatorObj->getCreatorUid($name);
            $album_creator_data = array();
            $album_creator_data['is_anchor'] = 1;
            if (empty($creator_uid)) {
                $creator_uid = $creatorObj->addCreator($name, "", "", $album_creator_data['is_author'], $album_creator_data['is_translator'], $album_creator_data['is_illustrator'], $album_creator_data['is_anchor']);
                $content = sprintf("新增主播[%s] : uid = %d \r\n", $name, $creator_uid);
                echo $content;

            } else {

                $whereArr = array(
                    "is_anchor" => 1,
                    "creator_uid" => $creator_uid,
                );
                $total_count = intval($creatorObj->getCreatorCount($whereArr));
                if($total_count == 0) {
                    $where = "uid = {$creator_uid}";
                    $ret = $creatorObj->update($album_creator_data, $where);
                    $content = sprintf("修复主播[%s] : uid = %d, is_anchor = %d, ret = %d\r\n", $name, $creator_uid,$album_creator_data['is_anchor'], $ret);
                    echo $content;
                }
            }

            //更新主播头像
            $user_info_list = $userObj->getUserInfo($creator_uid);
            if(!isset($user_info_list[$creator_uid]['avatartime']) || $user_info_list[$creator_uid]['avatartime'] <= 0) {
                if(!empty($xmly_album_info['anchor']['avatar']) && !empty($creator_uid)) {

                    $ret = $userObj->setAvatarWithUrl($xmly_album_info['anchor']['avatar'], $creator_uid);
                    $content = sprintf("更新主播[%s]头像[%s] \r\n", $creator_uid, $xmly_album_info['anchor']['avatar'], $ret);
                    echo $content;
                }
            }

            //更新故事主播信息
            $story_item_list = $storyObj->get_filed_list("id,author_uid,translator_uid,illustrator_uid,anchor_uid", "`album_id` = {$album_info['id']}");
            //检查作者信息
            $data = array();
            $data['anchor_uid'] = $creator_uid;
            if (is_array($story_item_list) && !empty($story_item_list) && !empty($data)) {
                foreach ($story_item_list as $k => $story_item) {
                    if (empty($story_item['anchor_uid'])) {
                        $story_id = $story_item['id'];
                        $where = "`id` = {$story_id}";
                        $ret = $storyObj->update($data, $where);
                        $content = sprintf("[%d]更新故事主播[%s] %d\r\n", $story_id, $data['anchor_uid'], $ret);
                        echo $content;
                    }
                }
            }
        }

        // 获取喜马拉雅的专辑故事
        $xmly_album_id = Http::sub_data($album_info['link_url'], 'album/');
        //var_dump($xmly_album_id);
        $story_url_list = $xmlyObj->get_story_url_list($xmly_album_id);
        $story_list_count = count($story_url_list);
        if (empty($story_url_list)) {

            $content = "喜马拉雅专辑{$album_info['id']} 没有故事\r\n";
            echo $content;

        } else if($album_info['story_num'] >= $story_list_count) {

            $ignore_count = $ignore_count + $album_info['story_num'];
            $content = sprintf("[%s] 故事总数量:%d, 已大于等于源内容 %d \r\n", $album_info['title'], $story_list_count, $ignore_count);
            echo $content;
            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_STORY, $content);

        } else {

            // 判断专辑简介是否为空，若为空则读取故事专辑下第一个故事的简介
            if (empty($album_info['intro'])) {
                $first_story_url = current($story_url_list);
                $first_story_info = $xmlyObj->get_story_info($first_story_url);
                if (!empty($first_story_info['intro'])) {
                    $albumObj->update(array("intro" => $first_story_info['intro']), "`id` = '{$album_info['id']}'");
                    $content = "喜马拉雅专辑{$album_info['id']} 简介更新成功\r\n";
                    echo $content;
                }
            }

            $vieworder = 0;
            foreach ($story_url_list as $key => $story_url) {
                // 默认故事的排序，按源页面故事排序
                $vieworder++;
                $story_url = $xmlyObj->get_story_info($story_url);
                if (!$story_url) {
                    $content = "$story_url_list[$key] 获取故事信息失败 \r\n";
                    echo $content;
                    continue;
                }
                $source_audio_path_arr = parse_url($story_url['source_audio_url']);
                //cdn根据域名做了切分
                $exists = $storyObj->check_exists(" LOCATE('{$source_audio_path_arr['path']}',`source_audio_url`) > 0");
                if ($exists) {
                    $ignore_count++;
                    $content = "$story_url_list[$key] 已存在 \r\n";
                    echo $content;
                    continue;
                }
                if (empty($vieworder)) {
                    $vieworder = $key;
                }

                $story_id = $storyObj->insert(array(
                    'album_id' => $album_info['id'],
                    'title' => addslashes($story_url['title']),
                    'intro' => addslashes($story_url['intro']),
                    'view_order' => $vieworder,
                    's_cover' => $story_url['s_cover'],
                    'source_audio_url' => $story_url['source_audio_url'],
                    'add_time' => date('Y-m-d H:i:s'),
                ));
                $storyUrlObj->insert(array(
                    'res_name' => 'story',
                    'res_id' => $story_id,
                    'field_name' => 'cover',
                    'source_url' => $story_url['s_cover'],
                    'source_file_name' => ltrim(strrchr($story_url['s_cover'], '/'), '/'),
                    'add_time' => date('Y-m-d H:i:s'),
                ));
                if ($story_id) {
                    $add_count++;
                    //MnsQueueManager::pushAlbumToSearchQueue($story_id);
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_XMLY_STORY, "{$story_id} 入库");
                } else {

                    $content = '没有写入成功' . var_export($album_info, true) . var_export($story_url, true);
                    echo $content;
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_XMLY_STORY, $content);
                }

                $content = "喜马拉雅专辑 {$album_info['id']} 新增 {$add_count}";
                echo $content;
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_STORY, $content);
                $albumObj->update_story_num($album_info['id']);
            }
        }
    }
}

new cron_xmlyStory();