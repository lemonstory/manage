<?php
/**
 * 奇数上传音频进程
 */
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_uploadAudio extends DaemonBase
{
    protected $processnum = 1;
    protected function deal() {
        $this->uploadAudio();
        exit;
    }

    protected function checkLogPath() {}


    protected function uploadAudio() {
        // 更新故事为本地地址
        $story = new Story();
        $story_list = $story->get_list("`mediapath`='' and `status`=1 ", 100);
        foreach ($story_list as $k => $v) {
        	if ($v['id']%2 == 1) {
        		$r = $this->middle_upload($v['source_audio_url'], $v['id'], 3);
                if (is_array($r) && $r) {
                    $story->update(array('mediapath' => $r['mediapath'], 'times' => $r['times'], 'file_size' => $r['size']), "`id`={$v['id']}");
                    $this->writeLog("故事 {$v['id']} => mediapath 更新成功");
                } else {
                    $this->writeLog("故事 {$v['id']} => mediapath 更新失败");
                }
        	}
            
        }

    }

    /**
     * 功能：php完美实现下载远程图片保存到本地
     * 将本地文件上传到oss,删除本地文件
     * type 1 专辑封面 2 故事封面 3 故事音频
     */
    private function middle_upload($url = '', $id = '', $type = ''){
        // 默认图片不上传
        if (strstr($url, 'default/album.jpg')) {
            return '';
        }
        if (strstr($url, 'default/sound.jpg')) {
            return '';
        }
        // 控制上传频率
        sleep(1);

        if (!$url || !$id || !$type) {
            return false;
        }

        $uploadobj = new Upload();
        $aliossobj = new AliOss();

        if ($type == 3) {
            $savedir = $aliossobj->LOCAL_MEDIA_TMP_PATH;
        } else {
            $savedir = $aliossobj->LOCAL_IMG_TMP_PATH;
        }

        $ext = strtolower(ltrim(strrchr($url,'.'), '.'));

        $filename = date("Y_m_d_{$type}_{$id}");

        $savedir = $savedir.date("Y_m_d_{$type}_{$id}");

        if(!in_array($ext, array('png', 'gif', 'jpg', 'jpeg', 'mp3', 'audio'))){
            return false;
        }

        $full_file = $savedir.'.'.$ext;

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

    protected function writeLog($content = '')
    {
        static $manageCollectionCronLog = null;
        if (!$manageCollectionCronLog) {
            $manageCollectionCronLog = new ManageCollectionCronLog();
        }
        $manageCollectionCronLog->insert(array('type' => 'upload_oss', 'content' => $content));
        
    }
}
new cron_uploadAudio();
