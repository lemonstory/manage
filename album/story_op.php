<?php
include_once '../controller.php';

class story_op extends controller
{
    public function action()
    {
        $op_name = $this->getRequest('op_name', '');
        $op_id = $this->getRequest('op_id', 0);

        if (!in_array($op_name, array('delete', 'recover', 'view_order', 'show', 'notshow', 'removealbum', 'add_story_to_album'))) {
            return $this->showErrorJson(ErrorConf::storyInfoIsEmpty());
        }
        $story = new Story();
        $manageStory = new ManageStory();
        $storyinfo = $manageStory->getStoryInfo($op_id);

        if (!$storyinfo) {
            return $this->showErrorJson(ErrorConf::storyInfoIsEmpty());
        }

        if ($op_name == 'delete') {
            $story->update(array('status' => 0), "`id`={$op_id}");
        } else if ($op_name == 'recover') {
            $story->update(array('status' => 1), "`id`={$op_id}");
        } else if ($op_name == 'add_story_to_album') {
        	$albumid = (int)$this->getRequest('albumid', '');
        	$manageAlbum = new ManageAlbum();
        	$albuminfo = $manageAlbum->getAlbumInfo($albumid);
        	if (empty($albuminfo)) {
        		return $this->showErrorJson(ErrorConf::albumInfoIsEmpty());
        	}
            $story = new Story();
            $story->update(array('album_id' => $albumid), "`id`={$op_id}");
        } else if ($op_name == 'removealbum') {
            $story = new Story();
            $story->update(array('album_id' => 0), "`id`={$op_id}");
        } else if ($op_name == 'view_order') {
            $value = (int)$this->getRequest('value', 0);
            $story->update(array('view_order' => $value), "`id`={$op_id}");
        }
        // 清除专辑故事列表缓存
        $story->clearAlbumStoryListCache($storyinfo['album_id']);
        return $this->showSuccJson('操作成功');

    }
}
new story_op();
?>