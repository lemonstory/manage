<?php
include_once '../controller.php';

class story_edit extends controller
{
    public function action()
    {
    	$story = new Story();

        if ($_POST) {
        	$storyid    = (int)$this->getRequest('id');
        	$title      = $this->getRequest('title');
        	$intro      = $this->getRequest('intro');

        	$newstoryinfo = $storyinfo  = array();
        	if ($storyid) {
        		$storyinfo = $story->get_story_info($storyid);
        	}
        	if (!$storyinfo) {
        		return $this->showErrorJson(ErrorConf::storyInfoIsEmpty());
        	}
        	if (!$title) {
        		return $this->showErrorJson(ErrorConf::storyTitleNotEmpty());
        	} else {
                $newstoryinfo['title'] = $title;
            }

            $newstoryinfo['intro'] = addslashes($intro);


            $story->update($newstoryinfo, "`id`={$storyid}");
            // 清故事列表缓存
            $story->clearAlbumStoryListCache($storyinfo['album_id']);
            
            // 封面处理
            if (!empty($_FILES['cover'])) {
                $uploadobj = new Upload();
                $path = $uploadobj->uploadStoryImageByPost($_FILES['cover'], $storyid);
                if (!empty($path)) {
                    // 更新cover_time
                    $story->update(array(
                        'cover_time' => time(),
                        'cover'      => str_replace("album/", '', $path),
                    ), "`id`={$storyid}");
                }
            }
            $this->showSuccJson();
        }

        $storyid   = (int)$this->getRequest('id', 0);

        $storyinfo = array();

        if ($storyid) {
        	$storyinfo = $story->get_story_info($storyid);
        }
        if (!$storyinfo) {
        	return $this->showErrorJson(ErrorConf::storyInfoIsEmpty());
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('storyinfo', $storyinfo);
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("album/story_edit.html"); 

    }
}
new story_edit();
?>