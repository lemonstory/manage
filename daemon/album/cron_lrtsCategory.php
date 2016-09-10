<?php
/**
 * lrts分类采集
 */
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_lrtsCategory extends DaemonBase {
    protected $home_url = 'http://www.lrts.me/book/category/6/latest';
    protected $processnum = 1;
    protected function deal() {
        $this->c_lrts_category();
        exit;
    }

    protected function checkLogPath() {}

    protected function c_lrts_category() {
        $category = new Category();
        $lrts = new Lrts();
        $story_url = new StoryUrl();
        $manageCollectionCronLog = new ManageCollectionCronLog();
        $current_time = date('Y-m-d H:i:s');
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, ManageCollectionCronLog::TYPE_LRTS_CATEGORY, "采集懒人听书分类开始");
        //分类
        $category_list = $lrts->get_category($this->home_url);
        $category_count = count($category_list);
        $ignore_count = 0;
        $add_count = 0;
        foreach ($category_list as $k => $v) {
            $exist = $category->check_exists("`res_name`='lrts' and `title`='{$v['title']}'");
            if ($exist) {
                $ignore_count++;
                continue;
            }
            $category_id = $category->insert(array(
                'res_name'  => 'lrts',
                'title'     => $v['title'],
                'parent_id' => '0',
                'from_url'  => $this->home_url,
                'link_url'  => $v['link_url'],
                's_cover'   => $v['cover'],
                'add_time'   => $current_time,
            ));

            if(!empty($v['cover'])) {
                $story_url->insert(array(
                    'res_name' => 'category',
                    'res_id' => $category_id,
                    'field_name' => 'cover',
                    'source_url' => $v['cover'],
                    'source_file_name' => ltrim(strrchr($v['cover'], '/'), '/'),
                    'add_time' => date('Y-m-d H:i:s'),
                ));
            }

            if ($category_id) {
                $add_count++;
                $tagnewobj = new TagNew();
                $tagnewobj->addTag($v['title'], 0);
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_LRTS_CATEGORY, "{$category_id} 入库");
            } else {
                $content = '没有写入成功'.var_export($v, true);
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_LRTS_CATEGORY, $content);
            }
        }

        $content = sprintf("分类总数量:%d, 已忽略 %d, 新增 %d", $category_count, $ignore_count, $add_count);
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_LRTS_CATEGORY, $content);
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, ManageCollectionCronLog::TYPE_LRTS_CATEGORY, "采集懒人听书分类结束");
    }
}
new cron_lrtsCategory();