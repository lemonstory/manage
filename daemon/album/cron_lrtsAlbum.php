<?php
/**
 * lrts专辑采集
 */
include_once(dirname(dirname(__FILE__)) . "/DaemonBase.php");

class cron_lrtsAlbum extends DaemonBase
{
    protected $processnum = 1;

    protected function deal()
    {
        $this->c_lrts_album();
        exit;
    }

    protected function checkLogPath()
    {
    }

    protected function c_lrts_album()
    {
        
        $lrtsObj = new Lrts();
        $albumObj = new Album();
        $categoryObj = new Category();
        $storyUrlObj = new StoryUrl();
        $manageObj = new ManageSystem();
        $tagNewObj = new TagNew();
        $manageCollectionCronLog = new ManageCollectionCronLog();
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, ManageCollectionCronLog::TYPE_LRTS_ALBUM, "采集懒人听书专辑开始");
        $current_time = date('Y-m-d H:i:s');
        // 分类
        $category_list = $categoryObj->get_list("`res_name`='lrts'");

        foreach ($category_list as $ck => $category) {
            $page = 1;
            $album_list_count = 0;
            $ignore_count = 0;
            $add_count = 0;
            while (true) {
                $album_list = $lrtsObj->get_album_list($category['link_url'], $page);
                if (!$album_list) {
                    break;
                }
                foreach ($album_list as $ak => $album) {
                    $exists = $albumObj->check_exists("`link_url` = '{$album['url']}'");
                    if ($exists) {
                        $ignore_count++;
                        continue;
                    }

                    $album_id = $albumObj->insert(array(
                        'title' => $album['title'],
                        'from' => 'lrts',
                        'intro' => '',
                        'category_id' => $category['id'],
                        's_cover' => $album['cover'],
                        'link_url' => $album['url'],
                        'add_time' => date('Y-m-d H:i:s'),
                    ));
                    // 最新上架
                    if ($album_id) {
                        $add_count++;
                        $manageObj->addRecommendNewOnlineDb($album_id, 0);
                        // add album tag
                        $tagNewObj->addAlbumTag($album_id, $category['title'], $category['parent_id']);
                    }
                    $storyUrlObj->insert(array(
                        'res_name' => 'album',
                        'res_id' => $album_id,
                        'field_name' => 'cover',
                        'source_url' => $album['cover'],
                        'source_file_name' => ltrim(strrchr($album['cover'], '/'), '/'),
                        'add_time' => date('Y-m-d H:i:s'),
                    ));
                    if ($album_id) {
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_LRTS_ALBUM, "{$album_id} 入库");
                    } else {
                        $content = '没有写入成功' . var_export($category, true) . var_export($album, true);
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_LRTS_ALBUM, $content);
                    }
                }
                $page++;
                $album_list_count = $album_list_count + count($album_list);
            }
            $content = sprintf("[{$category['title']}] 下有专辑数量:%d, 已忽略 %d, 新增 %d", $album_list_count, $ignore_count, $add_count);
            echo $content . "\r\n";
            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_LRTS_ALBUM, $content);
        }
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, ManageCollectionCronLog::TYPE_LRTS_ALBUM, "采集懒人听书专辑结束");
    }
}

new cron_lrtsAlbum();