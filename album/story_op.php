<?php
include_once '../controller.php';

class story_op extends controller
{
    public function action()
    {
        $op_name = $this->getRequest('op_name', '');
        $op_id = $this->getRequest('op_id', 0);

        if (!in_array($op_name, array('delete', 'recover', 'view_order', 'show', 'notshow'))) {
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
        	// 
        	unset($storyinfo['id']);
        	$storyinfo['album_id'] = $albuminfo['id'];
        	$storyinfo['add_time'] = date('Y-m-d H:i:s');
        	$storyinfo['update_time'] = date('Y-m-d H:i:s');
        	// $story->insert($storyinfo);
        }
        return $this->showSuccJson('操作成功');

    }
}
new story_op();
?>