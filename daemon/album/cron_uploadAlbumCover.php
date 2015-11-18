<?php
/**
 * 资源上传到OSS
 */
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_uploadAlbumCover extends DaemonBase
{
    protected $processnum = 1;
    protected $isWhile    = false;      //启动后运行一次deal还是循环运行
    protected function deal() {
        $this->uploadAlbumCover();
        exit;
    }

    protected function checkLogPath() {}


    protected function uploadAlbumCover() {
        $page = 1;
        $perpage = 1000;
        // 更新故事封面
        $db = DbConnecter::connectMysql('share_story');
        $album      = new Album();
        $aliossobj  = new AliOss();
        $alioss_sdk = new alioss_sdk();

        $sql = "SELECT id,`cover` FROM `album` WHERE `id` <=7000 order by id asc ";
        $st = $db->query( $sql );
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $album_list = $st->fetchAll();

        $bucket = $aliossobj->OSS_BUCKET_IMAGE;

        foreach ($album_list as $k => $v) {
            if ($v['cover']) {
                $from = $v['cover'];
                $to = "album/".$v['cover'];

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
        $manageCollectionCronLog->insert(array('type' => 'copy_album_cover', 'content' => $content));
        
    }
}
new cron_uploadAlbumCover();
