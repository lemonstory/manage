<?php
include_once '../controller.php';

class story_add extends controller
{
    public function action()
    {
        $albumObj = new Album();
        $storyObj = new Story();
        $uploadObj = new Upload();
        $aliossObj = new AliOss();

        $album_id = (int)$this->getRequest('album_id');
        if ($_POST) {

            session_start();
            $title = $this->getRequest('title');
            $intro = $this->getRequest('intro');
            $view_order = 0;
            $times = 0;
            $s_cover = '';
            $source_audio_url = null;
            $author_uid = null;
            $translator_uid = null;
            $illustrator_uid = null;
            $anchor_uid = null;
            $add_time = date('Y-m-d H:i:s');

            //添加故事信息
            $story_id = $storyObj->insert(array(
                'album_id' => $album_id,
                'title' => addslashes($title),
                'intro' => $intro,
                'view_order' => $view_order,
                'times' => $times,
                's_cover' => $s_cover,
                'source_audio_url' => $source_audio_url,
                'author_uid' => $author_uid,
                'translator_uid' => $translator_uid,
                'illustrator_uid' => $illustrator_uid,
                'anchor_uid' => $anchor_uid,
                'add_time' => $add_time,
            ));

            $story_update_info = array();
            //上传音频
            if (!empty($_FILES['story'])) {

                $savedir = $aliossObj->LOCAL_MEDIA_TMP_PATH;
                $type = 3;

                $ext = strtolower(ltrim(strrchr($_FILES['story']['name'], '.'), '.'));
                $length = strpos($ext, "?");
                if ($length && $length > 0) {
                    $ext = substr($ext, 0, $length);
                }
                $file_name = date("Y_m_d_{$type}_{$story_id}");
                if (!in_array($ext, array('mp3', 'audio'))) {
                    $content = "故事 {$story_id} => 不支持扩展名 {$ext}";
                    echo $content . "\r\n";
                }
                $file_path = $savedir . $file_name . '.' . $ext;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }

                if (move_uploaded_file($_FILES["story"]["tmp_name"], $file_path)) {
                    $res = $uploadObj->uploadStoryMedia($file_name, $ext, $story_id);
                    if (is_array($res)) {

                        $story_update_info['mediapath'] = $res['mediapath'];
                        $story_update_info['times'] = $res['times'];
                        $story_update_info['file_size'] = $res['size'];
                    }
                } else {
                    echo "音频文件上传失败";
                }
            }

            // 封面处理
            if (!empty($_FILES['cover'])) {
                $path = $uploadObj->uploadStoryImageByPost($_FILES['cover'], $story_id);
                if (!empty($path)) {
                    // 更新cover_time
                    $story_update_info['cover_time'] = time();
                    $story_update_info['cover'] = str_replace("story/", '', $path);
                }
            }

            //更新故事信息
            $storyObj->update($story_update_info, "`id`={$story_id}");

            //更新专辑内故事数量
            $albumObj->update_story_num($album_id);

            // 清故事列表缓存
            $storyObj->clearAlbumStoryListCache($album_id);
            MnsQueueManager::pushAlbumToSearchQueue($story_id);
            $this->showSuccJson($story_update_info);

        } else {

            $session_upload_progress_name = ini_get('session.upload_progress.name');
            $smartyObj = $this->getSmartyObj();
            $smartyObj->assign("album_id", $album_id);
            $smartyObj->assign("session_upload_progress_name",$session_upload_progress_name);
            $smartyObj->assign("headerdata", $this->headerCommonData());
            $smartyObj->display("album/story_add.html");
        }
    }
}

new story_add();
?>