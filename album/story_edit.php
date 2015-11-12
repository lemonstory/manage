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
        	if (!$intro) {
				return $this->showErrorJson(ErrorConf::storyIntroNotEmpty());
        	} else {
                $newstoryinfo['intro'] = $intro;
            }

            $story->update($newstoryinfo, "`id`={$storyid}");
            
            // 封面的更新
            if (isset($_FILES['cover']) && $_FILES['cover']['name']) {
                $uploadobj = new Upload();
                $aliossobj = new AliOss();
                $ext = getFileExtByMime($_FILES['cover']['type']);
                if (!$ext) {
                    return $this->showErrorJson(ErrorConf::storyCoverExtError());
                }
                $savedir = $uploadobj->getAlbumImageTmpDir();
                $full_filename = $savedir.date("Y_m_d_2_{$storyid}").'.'.$ext;
                if (file_exists($full_filename)) {
                    @unlink($full_filename);
                }

                move_uploaded_file($_FILES['cover']['tmp_name'], $full_filename);

                $res = $uploadobj->uploadAlbumImage($full_filename, $ext, $storyid);
                if (isset($res['path']) && $res['path']) {
                    $story->update(array('cover' => $res['path']), "`id`={$storyid}");
                }
            }

            return $this->showSuccJson('操作成功');
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