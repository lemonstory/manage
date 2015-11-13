<?php
/**
 * 资源上传到OSS
 */
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_uploadStoryCover extends DaemonBase
{
    protected $processnum = 1;
    protected $isWhile 	  = false;		//启动后运行一次deal还是循环运行
    protected function deal() {
        $this->uploadStoryCover();
        exit;
    }

    protected function checkLogPath() {}


    protected function uploadStoryCover() {
    	$page = 1;
    	$perpage = 1000;
        // 更新故事封面
        $story     = new Story();
        $aliossobj = new AliOss();

        while (true) {
        	$limit = ($page - 1) * $perpage;

        	$story_list = $story->get_list("id > 0", "{$limit}, {$perpage}");
        	if (!$story_list) {
        		break;
        	}

	        foreach ($story_list as $k => $v) {
	        	if ($v['cover']) {
	        		$from = $v['cover'];
			        $to = "story/".$v['cover'];
			        $aliossobj->copyImageOss($from, $to);
	        	}
	        }

	        echo "{$limit}, {$perpage}\n";
        	$page++;
        }
        

        return true;
    }


    protected function writeLog($content = '')
    {
        static $manageCollectionCronLog = null;
        if (!$manageCollectionCronLog) {
            $manageCollectionCronLog = new ManageCollectionCronLog();
        }
        $manageCollectionCronLog->insert(array('type' => 'upload_oss', 'content' => $content));
        
    }
}
new cron_uploadStoryCover();
