<?php
//抓取所有(或某一个)专辑
//
//业务逻辑:
//      根据输入参考u(可选)抓取专辑信息
//      u -album_url(例如:http://m.ximalaya.com/1870065/album/4975103) 存在时抓取该专辑,不存在时抓取所有专辑(依赖于category)
//使用:
//  php your_path/cron_xmlyAlbum.php -uhttp://m.ximalaya.com/1870065/album/4975103
//
include_once(dirname(dirname(__FILE__)) . "/DaemonBase.php");


class cron_xmlyAlbum extends DaemonBase
{
    protected $processnum = 1;

    protected function deal()
    {

        $options = getopt("u::");
        if (!empty($options)) {

            $url = $options['u'];
        }
        $album_url = trim($url);
        $this->c_xmly_album($album_url);
        exit;
    }

    protected function checkLogPath()
    {
    }

    protected function c_xmly_album($album_url)
    {
        $xmly = new Xmly();
        $album = new Album();
        $category = new Category();
        $story_url = new StoryUrl();
        $manageobj = new ManageSystem();
        $tagnewobj = new TagNew();
        $manageCollectionCronLog = new ManageCollectionCronLog();
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, ManageCollectionCronLog::TYPE_XMLY_ALBUM, "采集喜马拉雅专辑开始");
        $current_time = date('Y-m-d H:i:s');
        $ignore_count = 0;
        $add_count = 0;

        if (empty($album_url)) {
            // 分类
            $category_list = $category->get_list("`res_name`='xmly'");

            foreach ($category_list as $k => $v) {
                $page = 1;
                $album_list_count = 0;
                $ignore_count = 0;
                $add_count = 0;
                while (true) {
                    $album_list = $xmly->get_album_list($page, $v['title']);
                    if (!$album_list) {
                        break;
                    }
                    foreach ($album_list as $k2 => $v2) {
                        $exists = $album->check_exists("`link_url` = '{$v2['url']}'");
                        if ($exists) {
                            $ignore_count++;
                            continue;
                        }

                        $album_id = $album->insert(array(
                            'title' => $v2['title'],
                            'from' => 'xmly',
                            'intro' => '',
                            'category_id' => $v['id'],
                            's_cover' => $v2['cover'],
                            'link_url' => $v2['url'],
                            'add_time' => date('Y-m-d H:i:s'),
                        ));
                        // 最新上架
                        if ($album_id) {
                            $add_count++;
                            $manageobj->addRecommendNewOnlineDb($album_id, 0);
                            // add album tag
                            $tagnewobj->addAlbumTag($album_id, $v['title'], $v['parent_id']);
                        }
                        $story_url->insert(array(
                            'res_name' => 'album',
                            'res_id' => $album_id,
                            'field_name' => 'cover',
                            'source_url' => $v2['cover'],
                            'source_file_name' => ltrim(strrchr($v2['cover'], '/'), '/'),
                            'add_time' => date('Y-m-d H:i:s'),
                        ));
                        if ($album_id) {
                            $content = "{$album_id} 入库\r\n";
                            echo $content;
                            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_XMLY_ALBUM, $content);
                        } else {
                            $content = '没有写入成功' . var_export($v, true) . var_export($v2, true);
                            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_XMLY_ALBUM, $content);
                        }
                    }
                    $page++;
                    $album_list_count = $album_list_count + count($album_list);
                }
                $content = sprintf("[{$v['title']}] 下有专辑数量:%d, 已忽略 %d, 新增 %d", $album_list_count, $ignore_count, $add_count);
                echo $content . "\r\n";
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_ALBUM, $content);
            }
        } else {

            $exists = $album->check_exists("`link_url` = '{$album_url}'");
            if ($exists) {
                $content = $album_url . " 专辑已经存在\r\n";
                echo $content;
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_ALBUM, $content);
            } else {

                $album_info = $xmly->get_album_info($album_url);
                if (!empty($album_info['title']) && !empty($album_info['cover'])) {
                    $album_id = $album->insert(array(
                        'title' => $album_info['title'],
                        'from' => 'xmly',
                        'intro' => '',
                        'category_id' => 0,
                        's_cover' => $album_info['cover'],
                        'link_url' => $album_url,
                        'add_time' => date('Y-m-d H:i:s'),
                    ));
                    // 最新上架
                    if ($album_id) {
                        $add_count++;
                        $manageobj->addRecommendNewOnlineDb($album_id, 0);
                        // add album tag
                    }
                    $story_url->insert(array(
                        'res_name' => 'album',
                        'res_id' => $album_id,
                        'field_name' => 'cover',
                        'source_url' => $album_info['cover'],
                        'source_file_name' => ltrim(strrchr($album_info['cover'], '/'), '/'),
                        'add_time' => date('Y-m-d H:i:s'),
                    ));
                    if ($album_id) {
                        $content = "{$album_id} 入库\r\n";
                        echo $content;
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_XMLY_ALBUM,$content);
                    } else {
                        $content = '没有写入成功' . var_export($album_info, true);
                        echo $content;
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_XMLY_ALBUM, $content);
                    }
                }else {
                    $content = $album_url . " 获取专辑名称及封面为空\r\n";
                    echo $content;
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_ALBUM, $content);
                }
            }
        }
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, ManageCollectionCronLog::TYPE_XMLY_ALBUM, "采集喜马拉雅专辑结束");
    }
}

new cron_xmlyAlbum();