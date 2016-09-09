<?php
/**
 * 口袋故事专辑采集
 */
include_once(dirname(dirname(__FILE__)) . "/DaemonBase.php");

class cron_kdgsAlbum extends DaemonBase
{
    protected $processnum = 1;

    protected function deal()
    {
        $this->c_kdgs_album();
        exit;
    }

    protected function checkLogPath()
    {
    }

    protected function c_kdgs_album()
    {

        $kdgs = new Kdgs();
        $album = new Album();
        $category = new Category();
        $story_url = new StoryUrl();
        $manageobj = new ManageSystem();
        $tagnewobj = new TagNew();
        $manageCollectionCronLog = new ManageCollectionCronLog();
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, ManageCollectionCronLog::TYPE_KDGS_ALBUM, '采集口袋故事专辑执行开始');
        $category_list = $category->get_list("`res_name`='kdgs' and `parent_id`>0");
        foreach ($category_list as $k => $v) {
            $page = 1;
            $album_list_count = 0;
            $ignore_count = 0;
            $add_count = 0;
            while (true) {
                $album_list = $kdgs->get_children_category_album_list($v['s_p_id'], $page);
                if (!$album_list) {
                    break;
                }
                foreach ($album_list as $k2 => $v2) {
                    $exists = $album->check_exists("`link_url` = '{$v2['url']}'");
                    if ($exists) {
                        $ignore_count++;
                        continue;
                    }

                    $age_type = $album->get_age_type($v2['age_str']);
                    $age_str_arr = $album->get_age_arr($v2['age_str']);
                    $album_id = $album->insert(array(
                        'title' => $v2['title'],
                        'category_id' => $v['id'],
                        'from' => 'kdgs',
                        'intro' => '',
                        's_cover' => $v2['cover'],
                        'link_url' => $v2['url'],
                        'age_str' => $v2['age_str'],
                        'min_age' => intval($age_str_arr[0]),
                        'max_age' => intval($age_str_arr[1]),
                        'age_type' => $age_type,
                        'add_time' => date('Y-m-d H:i:s'),
                    ));

                    if ($album_id) {

                        $add_count++;
                        // 最新上架
                        $manageobj->addRecommendNewOnlineDb($album_id, $age_type);
                        // add album tag
                        $tagnewobj->addAlbumTag($album_id, $v['title'], $v['parent_id']);
                        $story_url->insert(array(
                            'res_name' => 'album',
                            'res_id' => $album_id,
                            'field_name' => 'cover',
                            'source_url' => $v2['cover'],
                            'source_file_name' => ltrim(strrchr($v2['cover'], '/'), '/'),
                            'add_time' => date('Y-m-d H:i:s'),
                        ));
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_KDGS_ALBUM, "{$album_id} 入库");

                    } else {

                        $content = '没有写入成功' . var_export($v, true) . var_export($v2, true);
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_KDGS_ALBUM, $content);
                    }

                }

                $page++;
                $album_list_count = $album_list_count + count($album_list);
            }
            $content = sprintf("[{$v['title']}] 下有专辑数量:%d, 已忽略 %d, 新增 %d", $album_list_count, $ignore_count, $add_count);
            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_KDGS_ALBUM, $content);
        }
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, ManageCollectionCronLog::TYPE_KDGS_ALBUM, '采集口袋故事专辑执行结束');
    }
}

new cron_kdgsAlbum();