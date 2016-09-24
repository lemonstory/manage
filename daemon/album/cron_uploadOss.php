<?php
/**
 * 资源上传到OSS
 */
include_once(dirname(dirname(__FILE__)) . "/DaemonBase.php");

class cron_uploadOss extends DaemonBase
{
    protected $home_url = 'http://m.idaddy.cn/mobile.php?etr=touch&mod=freeAudio&hidden=';
    protected $replacelist = array("_web_large", "_mobile_large", "_s150", "_s540");
    protected $processnum = 1;
    protected $isWhile = false;

    protected function deal()
    {
        $this->uploadOss();
        exit;
    }

    protected function checkLogPath()
    {
    }


    protected function uploadOss()
    {

        $manageCollectionCronLog = new ManageCollectionCronLog();
        // 更新分类封面
        /* $category = new Category();
        $category_list = $category->get_list("cover=''", '', 20);
        if (!empty($category_list)) {
            // 存已经上传的缓存
            $image_cache = array();
            foreach ($category_list as $k => $v) {
                if (isset($image_cache[$v['s_cover']])) {
                    $this->writeLog("分类封面(重复) {$v['id']} => cover 更新成功");
                } else {
                    $r = $this->middle_upload($v['s_cover'], $v['id'], 4);
                    if (is_string($r)) {
                        $r = str_replace("category/", "", $r);
                        $category->update(array('cover' => $r, 'cover_time' => time()), "`s_cover`='{$v['s_cover']}'");
                        $image_cache[$v['s_cover']] = $r;
                        $this->writeLog("分类封面 {$v['id']} => cover 更新成功");
                    } else {
                        $this->writeLog("分类封面 {$v['id']} => cover 更新失败");
                    }
                }
            }
        } */
        // 更新专辑封面
        $album = new Album();
        $album_list = $album->get_list("s_cover!='' and cover=''", 20);
        if (!empty($album_list)) {
            // 存已经上传的缓存
            $image_cache = array();
            foreach ($album_list as $k => $v) {
                if (isset($image_cache[$v['s_cover']])) {
                    $content = "专辑封面(重复) {$v['id']} => cover 更新成功\r\n";
                    echo $content;
                    //$manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_UPLOAD_OSS, $content);
                } else {
                    // 获取原图地址
                    $oricover = $v['s_cover'];
                    echo $oricover . "\r\n";
                    foreach ($this->replacelist as $searchstr) {
                        $oricover = str_replace($searchstr, "", $oricover);
                    }
                    // 上传原图
                    $r = $this->middle_upload($oricover, $v['id'], 1);
                    if (is_string($r)) {
                        $r = str_replace("album/", "", $r);
                        // 更新cover字段
                        $album->update(array('cover' => $r, 'cover_time' => time()), "`s_cover`='{$v['s_cover']}' and `cover`=''");
                        $image_cache[$v['s_cover']] = $r;
                        $content = "专辑封面 {$v['id']} => cover 更新成功\r\n";
                        echo $content;
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_UPLOAD_OSS, $content);
                        $album->clearAlbumCache($v['id']);
                    } else {
                        $content = "专辑封面 {$v['id']} => cover 更新失败\r\n";
                        echo $content;
                        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_UPLOAD_OSS, $content);
                    }
                }
            }
        }

        // 更新故事封面
        $story = new Story();
        //TODO:有10w+的图片未上传
        for($i=0;$i<20;$i++) {
            $story_list = $story->get_list("s_cover!='' AND cover='' AND LOCATE ('default/bg_player.jpg',`s_cover`) = 0 AND LOCATE ('default/sound.jpg',`s_cover`) = 0 AND `status` = 1", 5000);
            if (!empty($story_list)) {
                // 存已经上传的缓存
                $image_cache = array();
                foreach ($story_list as $k => $v) {

                    if (isset($image_cache[$v['s_cover']])) {
                        $content = "故事封面(重复) {$v['id']} => cover 更新成功\r\n";
                        echo $content;
                        //$manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_UPLOAD_OSS, $content);
                    } else {
                        $oricover = $v['s_cover'];
                        foreach ($this->replacelist as $searchstr) {
                            $oricover = str_replace($searchstr, "", $oricover);
                        }
                        $r = $this->middle_upload($oricover, $v['id'], 2);
                        if (is_string($r)) {
                            $r = str_replace("story/", "", $r);
                            $story->update(array('cover' => $r, 'cover_time' => time()), "`cover` = '' and `s_cover`='{$v['s_cover']}'");
                            $image_cache[$v['s_cover']] = $r;
                            $content = "故事封面 {$v['id']} => cover 更新成功\r\n";
                            echo $content;
                            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, ManageCollectionCronLog::TYPE_UPLOAD_OSS, $content);
                            $story->clearAlbumStoryListCache($v['album_id']);
                            $story->clearStoryCache($v['id']);
                        } else {
                            $content = "故事封面 {$v['id']} => cover 更新失败\r\n";
                            echo $content;
                            $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_UPLOAD_OSS, $content);
                        }
                    }
                }
            }
        }
    }

    /**
     * 功能：php完美实现下载远程图片保存到本地
     * 将本地文件上传到oss,删除本地文件
     * type 1 专辑封面 2 故事封面 3 故事音频 4 故事封面
     */
    private function middle_upload($url = '', $id = '', $type = '')
    {
        echo sprintf("url : %s, id : %s, type: %s \r\n", $url, $id, $type);
        $manageCollectionCronLog = new ManageCollectionCronLog();
        // 默认图片不上传
        if (strstr($url, 'default/album.jpg')) {
            return '';
        }
        if (strstr($url, 'default/sound.jpg')) {
            return '';
        }

        if (strstr($url, 'default/bg_player.jpg')) {
            return '';
        }
        // 控制上传频率
        sleep(1);

        if (!$url || !$id || !$type) {
            $content = "{middle_upload} 参数错误";
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
        $length = strpos($ext, "?");
        if ($length && $length > 0) {
            $ext = substr($ext, 0, $length);
        }
        $filename = date("Y_m_d_{$type}_{$id}");
        $savedir = $savedir . date("Y_m_d_{$type}_{$id}");

        if (!in_array($ext, array('png', 'gif', 'jpg', 'jpeg', 'mp3', 'audio', 'bmp'))) {
            if (strstr($url, 'mobile_large')) {
                $ext = 'jpg';
            } else {

                $content = "故事 {$id} => 下载失败,不支持扩展名 {$ext}";
                echo $content . "\r\n";
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_FAIL, ManageCollectionCronLog::TYPE_UPLOAD_OSS, $content);
                return false;

            }
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
            if ($type == 1) {
                // 专辑
                $res = $uploadobj->uploadAlbumImage($filename, $ext, $id);
            } else if ($type == 2) {
                // 故事
                $res = $uploadobj->uploadStoryImage($filename, $ext, $id);
            } else if ($type == 4) {
                // 分类
                $res = $uploadobj->uploadCategoryImage($filename, $ext, $id);
            }
            if (isset($res['path'])) {
                return $res['path'];
            }
        }

        return '';
    }
}

new cron_uploadOss();
