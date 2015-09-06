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
        		return $this->showErrorJson('不存在的专辑');
        	}
        	if (!$title) {
        		return $this->showErrorJson('title-标题不能为空');
        	} else {
                $newstoryinfo['title'] = $title;
            }
        	if (!$intro) {
				return $this->showErrorJson('intro-简介不能为空');
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