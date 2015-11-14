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
        $db = DbConnecter::connectMysql('share_story');
        $story      = new Story();
        $aliossobj  = new AliOss();
        $alioss_sdk = new alioss_sdk();

    	$sql = "SELECT id,`cover` FROM `story` WHERE `id` >=74962 and `cover` !='' GROUP BY `cover` ";
        $st = $db->query( $sql );
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $story_list = $st->fetchAll();

        $obj = new alioss_sdk();
        $bucket = $alioss_sdk->OSS_BUCKET_IMAGE;

        foreach ($story_list as $k => $v) {
        	if ($v['cover']) {
        		$from = $v['cover'];
		        $to = "story/".$v['cover'];

                $response = $alioss_sdk->copy_object($bucket, $from, $bucket, $to);

                if ($response->status==200){
                    $this->writeLog("{$v['id']} 复制成功");
                } else {
                    $this->writeLog("{$v['id']} 复制失败");
                }
        	}
        }
    
        return true;
    }


    protected function writeLog($content = '')
    {
        static $manageCollectionCronLog = null;
        if (!$manageCollectionCronLog) {
            $manageCollectionCronLog = new ManageCollectionCronLog();
        }
        $manageCollectionCronLog->insert(array('type' => 'copy_story_cover', 'content' => $content));
        
    }
}
new cron_uploadStoryCover();
