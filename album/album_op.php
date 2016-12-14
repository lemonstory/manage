<?php
include_once '../controller.php';

class index extends controller
{
    public function action()
    {
        $op_name = $this->getRequest('op_name', '');
        $op_id = $this->getRequest('op_id', 0);
        $open_search_obj = new OpenSearch();

        if (!in_array($op_name, array('delete', 'recover', 'view_order', 'online', 'notonline'))) {
            $this->showErrorJson("专辑数据为空");
        }
        $album = new Album();
        $albuminfo = $album->get_album_info($op_id);
        if (!$albuminfo) {
            $this->showErrorJson("专辑数据不存在");
        }

        $ret = true;
        if ($op_name == 'delete') {
            $album->update(array('status' => 0), "`id`={$op_id}");
            //$ret = $open_search_obj->removeAlbumFromSearch($op_id);//使用RDS数据源，无需手动同步
        } else if ($op_name == 'recover') {
            $album->update(array('status' => 1), "`id`={$op_id}");
            //$ret = $open_search_obj->addAlbumToSearchWithAlbumid($op_id);//使用RDS数据源，无需手动同步
        } else if ($op_name == 'view_order') {
            $value = (int)$this->getRequest('value', 0);
            $album->update(array('view_order' => $value), "`id`={$op_id}");
        } else if ($op_name == 'online') {
            $value = (int)$this->getRequest('value', 0);
            $album->update(array('online_status' => 1), "`id`={$op_id}");
        } else if ($op_name == 'notonline') {
            $value = (int)$this->getRequest('value', 0);
            $album->update(array('online_status' => 0), "`id`={$op_id}");
        }
        return $this->showSuccJson("操作成功 : ret = {$ret}");

    }
}
new index();
?>