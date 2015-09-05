<?php
include_once '../controller.php';

class index extends controller
{
	// @huqq delete
	public function filters()
    {
        return array(
            'authLogin' => array(
                'requireLogin' => false,
            ),
            'privilege' => array(
                'checkPrivilege' => false,
            ),
        );
    }
    
    public function action()
    {
        $op_name = $this->getRequest('op_name', '');
        $op_id = $this->getRequest('op_id', 0);

        if (!in_array($op_name, array('delete', 'recover', 'view_order'))) {
            $this->showErrorJson("专辑数据为空");
        }
        $album = new Album();
        $albuminfo = $album->get_album_info($op_id);
        if (!$albuminfo) {
            $this->showErrorJson("专辑数据不存在");
        }

        if ($op_name == 'delete') {
            $album->update(array('status' => 0), "`id`={$op_id}");
        } else if ($op_name == 'recover') {
            $album->update(array('status' => 1), "`id`={$op_id}");
        } else if ($op_name == 'view_order') {
            $value = (int)$this->getRequest('value', 0);
            $album->update(array('view_order' => $value), "`id`={$op_id}");
        }
        return $this->showSuccJson('操作成功');

    }
}
new index();
?>