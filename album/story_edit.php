<?php
include_once '../controller.php';

class index extends controller
{
    public function action()
    {
    	$story = new Story();

        if ($_POST) {
        	$id         = (int)$this->getRequest('id');
        	$title      = $this->getRequest('title');
        	$intro      = $this->getRequest('intro');

        	$newstoryinfo = $storyinfo  = array();
        	if ($id) {
        		$storyinfo = $story->get_story_info($id);
        	}
        	if (!$storyinfo) {
        		return $this->showErrorJson(ErrorConf::storyTitleNotEmpty());
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

            $story->update($newstoryinfo, "`id`={$id}");

            return $this->showSuccJson('操作成功');
        }

        $storyid   = (int)$this->getRequest('id', 0);

        $storyinfo = array();

        if ($storyid) {
        	$storyinfo = $story->get_story_info($storyid);
        }
        if (!$storyinfo) {
        	echo '不存在的信息';exit;
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('storyinfo', $storyinfo);
        $smartyObj->display("album/story_edit.html"); 

    }
}
new index();
?>