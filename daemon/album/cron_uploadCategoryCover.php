<?php
die();
/**
 * 资源上传到OSS
 */
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_uploadCategoryCover extends DaemonBase
{
    protected $processnum = 1;
    protected $isWhile 	  = false;		//启动后运行一次deal还是循环运行
    protected function deal() {
        $this->uploadCategoryCover();
        exit;
    }

    protected function checkLogPath() {}


    protected function uploadCategoryCover() {
    	if (isset($_SERVER['argv'][1])) {
    		$page = $_SERVER['argv'][1];
    	} else {
    		exit('page params error');
    	}
    	$perpage = 1000;
    	$limit = ($page - 1) * $perpage;
        // 更新故事封面
        $category  = new Category();
        $aliossobj = new AliOss();
        
        $category_list = $category->get_list("id > 0", '', "{$limit}, {$perpage}");

        foreach ($category_list as $k => $v) {
        	if ($v['cover']) {
        		$from = $v['cover'];
		        $to = "category/".$v['cover'];
		        $aliossobj->copyImageOss($from, $to);
        	}
        	
        }

        echo "{$limit}, {$perpage}\n";

        return true;
    }


    protected function writeLog($content = '')
    {
        static $manageCollectionCronLog = null;
        if (!$manageCollectionCronLog) {
            $manageCollectionCronLog = new ManageCollectionCronLog();
        }
        $manageCollectionCronLog->insert(array('type' => 'copy_category_cover', 'content' => $content));
        
    }
}
new cron_uploadCategoryCover();
