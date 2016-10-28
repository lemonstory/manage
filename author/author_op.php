<?php
include_once '../controller.php';

class index extends controller
{
    public function action()
    {
        $op_name = $this->getRequest('op_name', '');
        $op_id = $this->getRequest('op_id', 0);

        if (!in_array($op_name, array('view_order', 'online', 'notonline'))) {
            $this->showErrorJson("作者数据为空");
        }
        $creatorObj = new Creator();
        $whereArr = array(
            "is_author" => 1,
        );

        $authorInfo = array();
        $creatorList = $creatorObj->getCreatorList($whereArr, 1, 1);
        if (is_array($creatorList) && !empty($creatorList) && count($creatorList) == 1) {
            $authorInfo = $creatorList;
        }

        if (!$authorInfo) {
            $this->showErrorJson("作者数据不存在");
        }

        if ($op_name == 'view_order') {
            $value = (int)$this->getRequest('value', 0);
            $ret = $creatorObj->update(array('view_order' => $value), "`uid`={$op_id}");
        } else if ($op_name == 'online') {
            $value = (int)$this->getRequest('value', 0);
            $ret = $creatorObj->update(array('online_status' => 1), "`uid`={$op_id}");
        } else if ($op_name == 'notonline') {
            $value = (int)$this->getRequest('value', 0);
            $ret = $creatorObj->update(array('online_status' => 0), "`uid`={$op_id}");
        }
        return $this->showSuccJson("操作成功 : ret = {$ret}");

    }
}
new index();
?>