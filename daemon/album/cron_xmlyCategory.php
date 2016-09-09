<?php
/**
 * 喜马拉雅故事分类采集
 */
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_xmlyCategory extends DaemonBase {
    protected $home_url = 'http://m.ximalaya.com/album-tag/kid';
    protected $processnum = 1;
	protected function deal() {
		$this->c_xmly_category();
	    exit;
	}

	protected function checkLogPath() {}

	protected function c_xmly_category() {
        $category = new Category();
        $xmly = new Xmly();
        $story_url = new StoryUrl();
        $manageCollectionCronLog = new ManageCollectionCronLog();
        $current_time = date('Y-m-d H:i:s');
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, ManageCollectionCronLog::TYPE_XMLY_CATEGORY, "采集喜马拉雅分类开始");
        // 分类
        $category_list = $xmly->get_category($this->home_url);
        $category_count = count($category_list);
        $ignore_count = 0;
        $add_count = 0;
        foreach ($category_list as $k => $v) {
            $exist = $category->check_exists("`res_name`='xmly' and `title`='{$v['title']}'");
            if ($exist) {
                $ignore_count++;
                continue;
            }
        	$category_id = $category->insert(array(
        		'res_name'  => 'xmly',
        		'title'     => $v['title'],
        		'parent_id' => '0',
        		'from_url'  => $this->home_url,
        		'link_url'  => $v['link_url'],
                's_cover'   => $v['cover'],
                'add_time'   => $current_time,
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
                $tagnewobj = new TagNew();
                $tagnewobj->addTag($v['title'], 0);
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_XMLY_CATEGORY, "{$category_id} 入库");
            } else {
                $content = '没有写入成功'.var_export($v, true);
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_XMLY_CATEGORY, $content);
            }
        }

        $content = sprintf("分类总数量:%d, 已忽略 %d, 新增 %d", $category_count, $ignore_count, $add_count);
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_XMLY_CATEGORY, $content);
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, ManageCollectionCronLog::TYPE_XMLY_CATEGORY, "采集喜马拉雅分类结束");
    }
}
new cron_xmlyCategory();