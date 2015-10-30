<?php
/**
 * 资源上传到OSS
 */
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_uploadOss extends DaemonBase
{
    protected $home_url = 'http://m.idaddy.cn/mobile.php?etr=touch&mod=freeAudio&hidden=';
    protected $processnum = 1;
    protected function deal() {
        $this->uploadOss();
        exit;
    }

    protected function checkLogPath() {}


    protected function uploadOss() {
        // // 更新专辑封面
        $album = new Album();
        $album_list = $album->get_list("cover=''", 1);
        var_dump($album_list);
        foreach ($album_list as $k => $v) {
            $r = $this->middle_upload($v['s_cover'], $v['id'], 1);
            if (is_string($r)) {
                $album->update(array('cover' => $r), "`id`={$v['id']}");
            }
        }
        var_dump($r);
        // 更新故事封面
        $story = new Story();
        $story_list = $album->get_list("cover=''", 1);
        var_dump($story_list);
        foreach ($story_list as $k => $v) {
            $r = $this->middle_upload($v['s_cover'], $v['id'], 2);
            if (is_string($r)) {
                $story->update(array('cover' => $r), "`id`={$v['id']}");
            }
        }
        var_dump($r);
        // 更新故事为本地地址
        $story = new Story();
        $story_list = $story->get_list("mediapath=''", 1);
        var_dump($story_list);
        foreach ($story_list as $k => $v) {
            $r = $this->middle_upload($v['source_audio_url'], $v['id'], 3);
            if (is_array($r) && $r) {
                $story->update(array('mediapath' => $r['mediapath'], 'times' => $r['times'], 'file_size' => $r['size']), "`id`={$v['id']}");
            }
        }
        var_dump($r);
    }

    /**
     * 功能：php完美实现下载远程图片保存到本地
     * 将本地文件上传到oss,删除本地文件
     * type 1 专辑封面 2 故事封面 3 故事音频
     */
    private function middle_upload($url = '', $id = '', $type = ''){

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

        if(!in_array($ext, array('gif', 'jpg', 'jpeg', 'mp3', 'audio'))){
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
}
new cron_uploadOss();