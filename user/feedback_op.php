<?php
include_once '../controller.php';

class index extends controller
{
    public function action()
    {
        $op_name = $this->getRequest('op_name', '');
        $op_id = $this->getRequest('op_id', 0);

        if (!in_array($op_name, array('delete', 'recover', 'view_order', 'show', 'notshow'))) {
            $this->showErrorJson("专辑数据为空");
        }
        $feedback = new ManageUserFeedback();
        $feedbackinfo = $feedback->get_feedback_info($op_id);
        if (!$feedbackinfo) {
            $this->showErrorJson("专辑数据不存在");
        }

        if ($op_name == 'delete') {
            $feedback->update(array('status' => 0), "`id`={$op_id}");
        } else if ($op_name == 'recover') {
            $feedback->update(array('status' => 1), "`id`={$op_id}");
        } else if ($op_name == 'view_order') {
            $value = (int)$this->getRequest('value', 0);
            $feedback->update(array('view_order' => $value), "`id`={$op_id}");
        } else if ($op_name == 'show') {
            $value = (int)$this->getRequest('value', 0);
            $feedback->update(array('is_show' => 1), "`id`={$op_id}");
        } else if ($op_name == 'notshow') {
            $value = (int)$this->getRequest('value', 0);
            $feedback->update(array('is_show' => 0), "`id`={$op_id}");
        }
        return $this->showSuccJson('操作成功');

    }
}
new index();
?>