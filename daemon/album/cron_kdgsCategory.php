<?php
/**
 * 口袋故事分类采集
 */
include_once(dirname(dirname(__FILE__)) . "/DaemonBase.php");

class cron_kdgsCategory extends DaemonBase
{

    protected $home_url = 'http://m.idaddy.cn/mobile.php?etr=touch&mod=freeAudio&hidden=';
    protected $processnum = 1;

    protected function deal()
    {
        $this->c_kdgs_category();
        exit;
    }

    protected function checkLogPath()
    {

    }


    protected function c_kdgs_category()
    {
        // 子类
        $category = new Category();
        $kdgs = new Kdgs();
        $story_url = new StoryUrl();
        $tagnewobj = new TagNew();
        $manageCollectionCronLog = new ManageCollectionCronLog();

        $current_time = date('Y-m-d H:i:s');
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, ManageCollectionCronLog::TYPE_KDGS_CATEGORY, '口袋故事执行开始');
        $parent_category_list = $kdgs->get_parent_category($this->home_url);
        $parent_category_count = count($parent_category_list);
        $ignore_count = 0;
        $add_count = 0;

        //分类抓取
        foreach ($parent_category_list as $k => $v) {
            $exists = $category->check_exists("`res_name`='kdgs' and `s_id`='{$v['s_id']}'");
            if ($exists) {
                $ignore_count++;
                continue;
            } else {
                $category_id = $category->insert(array(
                    'res_name' => 'kdgs',
                    'parent_id' => 0,
                    'title' => $v['title'],
                    's_id' => $v['s_id'],
                    's_p_id' => 0,
                    'from_url' => $this->home_url,
                    'link_url' => $v['link_url'],
                    's_cover' => $v['cover'],
                    'add_time' => date('Y-m-d H:i:s')
                ));
                $story_url->insert(array(
                    'res_name' => 'category',
                    'res_id' => $category_id,
                    'field_name' => 'cover',
                    'source_url' => $v['cover'],
                    'source_file_name' => ltrim(strrchr($v['cover'], '/'), '/'),
                    'add_time' => date('Y-m-d H:i:s'),
                ));
                if ($category_id) {
                    $add_count++;
                    // add tag
                    $tagnewobj->addTag($v['title'], 0);
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_KDGS_CATEGORY, "{$category_id} 入库");

                } else {
                    $content = '没有写入成功' . var_export($v, true);
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_KDGS_CATEGORY, $content);

                }
            }
        }
        $content = sprintf("分类总数量:%d, 已忽略 %d, 新增 %d", $parent_category_count, $ignore_count, $add_count);
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_KDGS_CATEGORY, $content);

        //分类下的子类抓取
        $category_list = $category->get_list("`res_name`='kdgs' and `s_p_id`=0");
        foreach ($category_list as $k => $v) {
            $children_category_list = $kdgs->get_children_category_list($v['link_url']);
            $children_category_count = count($children_category_list);
            $ignore_count = 0;
            $add_count = 0;
            foreach ($children_category_list as $k2 => $v2) {
                $exists = $category->check_exists("`res_name`='kdgs' and `s_id`='{$v['s_id']}' and `s_p_id`='{$v2['s_p_id']}'");
                if ($exists) {
                    $ignore_count++;
                    continue;
                } else {
                    $category_id = $category->insert(array(
                        'res_name' => 'kdgs',
                        'parent_id' => $v['id'],
                        'title' => $v2['title'],
                        's_id' => $v['s_id'],
                        's_p_id' => $v2['s_p_id'],
                        'from_url' => $v['link_url'],
                        'link_url' => $v2['link_url'],
                        's_cover' => $v2['cover'],
                        'add_time' => date('Y-m-d H:i:s')
                    ));
                    $story_url->insert(array(
                        'res_name' => 'category',
                        'res_id' => $category_id,
                        'field_name' => 'cover',
                        'source_url' => $v2['cover'],
                        'source_file_name' => ltrim(strrchr($v2['cover'], '/'), '/'),
                        'add_time' => date('Y-m-d H:i:s'),
                    ));
                    if ($category_id) {
                        // add tag
                        $add_count++;
                        $tagnewobj->addTag($v2['title'], $v['id']);
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_KDGS_CATEGORY, "{$category_id} 入库");

                    } else {

                        $content = '没有写入成功' . var_export($v, true);
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_KDGS_CATEGORY, $content);

                    }
                }

            }
            $content = sprintf("[{$v['title']}] 下有子分类数量:%d, 已忽略 %d, 新增 %d", $children_category_count, $ignore_count, $add_count);
            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_KDGS_CATEGORY, $content);
        }
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, ManageCollectionCronLog::TYPE_KDGS_CATEGORY, '口袋故事执行结束');
    }
}

new cron_kdgsCategory();