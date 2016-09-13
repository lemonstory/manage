<?php
/**
 * 奇数上传音频进程
 */
include_once(dirname(dirname(__FILE__)) . "/DaemonBase.php");

class cron_uploadAudio extends DaemonBase
{
    protected $processnum = 10;

    protected function deal()
    {
        $options = getopt("m:r:");
        $mod = $options['m'];
        $rem = $options['r'];
        $this->uploadAudio($mod, $rem);
    }

    protected function checkLogPath()
    {
    }


    protected function uploadAudio($mod, $rem)
    {
        // 更新故事为本地地址
        $story = new Story();
        $manageCollectionCronLog = new ManageCollectionCronLog();
        $story_list = $story->get_list("`mediapath`='' and `status`=1 and MOD(`id`,{$mod})={$rem}", 10);
        foreach ($story_list as $k => $v) {

            //if ($v['id'] % $mod == $rem) {
                $headerarr = get_headers($v['source_audio_url']);
                if (!empty($headerarr)) {
                    $contentlengthheader = $headerarr[3];
                    if (!empty($contentlengthheader)) {
                        $contentlengtharr = explode(":", $contentlengthheader);
                        $length = intval(trim($contentlengtharr[1]));
                        if (!empty($length) && $length > 50000000) {
                            $content = sprintf("故事 %s => mediapath[%d] 大于50M\r\n",$v['id'],$length);
                            echo $content . "\r\n";
                            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_TRACK_LOG, ManageCollectionCronLog::TYPE_UPLOAD_OSS, $content);
                            continue;
                        }
                    }
                }

                $r = $this->middle_upload($v['source_audio_url'], $v['id'], 3);

                if (is_array($r) && $r) {
                    $story->update(array('mediapath' => $r['mediapath'], 'times' => $r['times'], 'file_size' => $r['size']), "`id`={$v['id']}");
                    MnsQueueManager::pushAlbumToSearchQueue($v['id']);
                    $content = sprintf("故事 %s => mediapath 更新成功\r\n",$v['id']);
                    echo $content . "\r\n";
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_UPLOAD_OSS, $content);
                } else {
                    $content = sprintf("[%s] 故事 %s => mediapath 更新失败\r\n",$v['id']);
                    echo $content . "\r\n";
                    $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_UPLOAD_OSS, $content);
                }

            //}
        }
    }

    /**
     * 功能：php完美实现下载远程图片保存到本地
     * 将本地文件上传到oss,删除本地文件
     * type 1 专辑封面 2 故事封面 3 故事音频
     */
    private function middle_upload($url = '', $id = '', $type = '')
    {

        echo sprintf("[%s] url : %s, id : %s, type: %s \r\n",date('Y-m-d H:i:s'), $url, $id, $type);
        $manageCollectionCronLog = new ManageCollectionCronLog();
        // 默认图片不上传
        if (strstr($url, 'default/album.jpg')) {
            return '';
        }
        if (strstr($url, 'default/sound.jpg')) {
            return '';
        }
        // 控制上传频率
        usleep(20);

        if (!$url || !$id || !$type) {
            $content = sprintf("{middle_upload} 参数错误");
            echo $content . "\r\n";
            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_UPLOAD_OSS, $content);
            return false;
        }

        $uploadobj = new Upload();
        $aliossobj = new AliOss();

        if ($type == 3) {
            $savedir = $aliossobj->LOCAL_MEDIA_TMP_PATH;
        } else {
            $savedir = $aliossobj->LOCAL_IMG_TMP_PATH;
        }

        $ext = strtolower(ltrim(strrchr($url, '.'), '.'));

        $filename = date("Y_m_d_{$type}_{$id}");

        $savedir = $savedir . date("Y_m_d_{$type}_{$id}");

        if (!in_array($ext, array('png', 'gif', 'jpg', 'jpeg', 'mp3', 'audio', 'm4a'))) {

            $content = sprintf("故事 %s => 下载失败,不支持扩展名 %s\r\n",$id,$ext);
            echo $content;
            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_UPLOAD_OSS, $content);
            return false;
        }

        $full_file = $savedir . '.' . $ext;

        if (file_exists($full_file)) {
            @unlink($full_file);
        }
        $file = Http::download($url, $full_file);

        if ($type == 3) {
            $res = $uploadobj->uploadStoryMedia($filename, $ext, $id);
            return $res;
        } else {
            $res = $uploadobj->uploadAlbumImage($filename, $ext, $id);
            if (isset($res['path'])) {
                return $res['path'];
            }
        }

        return '';

    }
}

new cron_uploadAudio();
