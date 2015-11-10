<?php
/**
 * 奇数上传音频进程
 */
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_updateStoryViewOrder extends DaemonBase
{
    protected $processnum = 1;
    protected function deal() {
        $this->updateStoryViewOrder();
        exit;
    }

    protected function checkLogPath() {}


    protected function updateStoryViewOrder() {
        set_time_limit(0);
        $story = new Story();
        $story_list = $story->get_filed_list("`id`,`title`", " `view_order`=0");
        foreach ($story_list as $k => $v) {
            $r = $story->get_view_order($v['title'], 1);
            if (!$r) {
                $r = $story->get_view_order($v['title'], 2);
            }
            $r = intval($r);
            if ($r) {
                if ($r) {
                    // $story->update(array('view_order' => $r), "`id`={$v['id']}");
                    $this->writeLog("故事ID:{$v['id']} title:{$v['title']} 更新为 {$r}");
                    echo "故事ID:{$v['id']} title:{$v['title']} 更新为 {$r}\n";
                    sleep(1);
                }
            }
        }
    }

    protected function writeLog($content = '')
    {
        static $manageCollectionCronLog = null;
        if (!$manageCollectionCronLog) {
            $manageCollectionCronLog = new ManageCollectionCronLog();
        }
        $manageCollectionCronLog->insert(array('type' => 'update_story_view_order', 'content' => $content));
        
    }
}
new cron_updateStoryViewOrder();
