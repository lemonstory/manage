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
        // 更新分类封面
        $category = new Category();
        $category_list = $category->get_list("cover=''", '', 500);
        if (!empty($category_list)) {
            // 存已经上传的缓存
            $image_cache = array();
            foreach ($category_list as $k => $v) {
                if (isset($image_cache[$v['s_cover']])) {
                    $this->writeLog("分类封面(重复) {$v['id']} => cover 更新成功");
                } else {
                    $r = $this->middle_upload($v['s_cover'], $v['id'], 1);
                    if (is_string($r)) {
                        $category->update(array('cover' => $r), "`s_cover`={$v['s_cover']}");
                        $image_cache[$v['s_cover']] = $r;
                        $this->writeLog("分类封面 {$v['id']} => cover 更新成功");
                    } else {
                        $this->writeLog("分类封面 {$v['id']} => cover 更新失败");
                    }
                }
            }
        }
        // 更新专辑封面
        $album = new Album();
        $album_list = $album->get_list("cover='' and s_cover!=''", 500);
        if (!empty($album_list)) {
            // 存已经上传的缓存
            $image_cache = array();
            foreach ($album_list as $k => $v) {
                if (isset($image_cache[$v['s_cover']])) {
                    $this->writeLog("专辑封面(重复) {$v['id']} => cover 更新成功");
                } else {
                    $r = $this->middle_upload($v['s_cover'], $v['id'], 1);
                    if (is_string($r)) {
                        $album->update(array('cover' => $r), "`s_cover`={$v['s_cover']}");
                        $image_cache[$v['s_cover']] = $r;
                        $this->writeLog("专辑封面 {$v['id']} => cover 更新成功");
                    } else {
                        $this->writeLog("专辑封面 {$v['id']} => cover 更新失败");
                    }
                }
            }
        }

        // 更新故事封面
        $story = new Story();
        $story_list = $story->get_list("cover=''", 500);
        if (!empty($story_list)) {
            // 存已经上传的缓存
            $image_cache = array();
            foreach ($story_list as $k => $v) {
                if (isset($image_cache[$v['s_cover']])) {
                    $this->writeLog("故事封面(重复) {$v['id']} => cover 更新成功");
                } else {
                    $r = $this->middle_upload($v['s_cover'], $v['id'], 2);
                    if (is_string($r)) {
                        $story->update(array('cover' => $r), "`s_cover`={$v['s_cover']}");
                        $image_cache[$v['s_cover']] = $r;
                        $this->writeLog("故事封面 {$v['id']} => cover 更新成功");
                    } else {
                        $this->writeLog("故事封面 {$v['id']} => cover 更新失败");
                    }
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

        if(!in_array($ext, array('png', 'gif', 'jpg', 'jpeg', 'mp3', 'audio', 'bmp'))){
            if (strstr($url, 'mobile_large')) {
                $ext = 'jpg';
            } else {
                return false;
            }
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
new cron_uploadOss();
