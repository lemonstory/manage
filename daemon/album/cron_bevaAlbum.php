<?php
/**
 * 专辑采集
 */
include_once(dirname(dirname(__FILE__)) . "/DaemonBase.php");

class cron_bevaAlbum extends DaemonBase
{
    protected $processnum = 1;
    protected $category_url_list = array(
        array(
            'title' => '儿歌',
            'min_age' => 0,
            'max_age' => 2,
            'url' => 'http://g.beva.com/mp3/topic/10001.html',),

        array(
            'title' => '音乐',
            'min_age' => 0,
            'max_age' => 6,
            'url' => 'http://g.beva.com/mp3/topic/10003.html',),
    );

    protected function deal()
    {
        $this->c_beva_album();
        exit;
    }

    protected function checkLogPath()
    {
    }

    protected function c_beva_album()
    {

        $bevaObj = new Beva();
        $albumObj = new Album();
        $storyUrlObj = new StoryUrl();
        $manageObj = new ManageSystem();
        $tagNewObj = new TagNew();
        $manageCollectionCronLog = new ManageCollectionCronLog();
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, ManageCollectionCronLog::TYPE_BEVA_ALBUM, '采集beva专辑执行开始');
        foreach ($this->category_url_list as $k => $val) {
            $page = 1;
            $album_list_count = 0;
            $ignore_count = 0;
            $add_count = 0;
            while (true) {
                $album_list = $bevaObj->get_album_list($val['url'], $page);
                if (is_array($album_list) && !empty($album_list)) {

                    foreach ($album_list as $key => $album) {
                        $exists = $albumObj->check_exists("`link_url` = '{$album['url']}'");
                        if (!$exists) {
                            $album_id = $albumObj->insert(array(
                                'title' => $album['title'],
                                'from' => 'beva',
                                'intro' => '',
                                's_cover' => $album['cover'],
                                'link_url' => $album['url'],
                                'min_age' => intval($this->category_url_list[$k]['min_age']),
                                'max_age' => intval($this->category_url_list[$k]['max_age']),
                                'add_time' => date('Y-m-d H:i:s'),
                            ));
                            if ($album_id) {

                                $add_count++;
                                // 最新上架
                                $manageObj->addRecommendNewOnlineDb($album_id, 1);
                                // add album tag
                                $tagNewObj->addAlbumTag($album_id, $this->category_url_list[$k]['title'], 0);
                                $storyUrlObj->insert(array(
                                    'res_name' => 'album',
                                    'res_id' => $album_id,
                                    'field_name' => 'cover',
                                    'source_url' => $album['cover'],
                                    'source_file_name' => ltrim(strrchr($album['cover'], '/'), '/'),
                                    'add_time' => date('Y-m-d H:i:s'),
                                ));
                                $content = "{$album_id} 入库 \r\n";
                                echo $content;
                                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_BEVA_ALBUM, $content);

                            } else {

                                $content = '没有写入成功' . var_export($val, true) . var_export($album, true);
                                echo $content;
                                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_BEVA_ALBUM, $content);
                            }

                        } else {
                            $ignore_count++;
                            continue;
                        }
                    }

                } else {

                    break;
                }


                $page++;
                $album_list_count = $album_list_count + count($album_list);
            }
            $content = sprintf("[{$val['title']}] 下有专辑数量:%d, 已忽略 %d, 新增 %d \r\n", $album_list_count, $ignore_count, $add_count);
            echo $content;
            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_BEVA_ALBUM, $content);
        }
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, ManageCollectionCronLog::TYPE_BEVA_ALBUM, '采集beva专辑执行结束');
    }
}

new cron_bevaAlbum();