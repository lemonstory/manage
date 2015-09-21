<?php
include_once '../controller.php';

class index extends controller
{
    public function action()
    {
        $op_name = $this->getRequest('op_name', '');
        $op_id   = (int)$this->getRequest('op_id', 0);
        $content = $this->getRequest('content', '');

        if (!in_array($op_name, array('delete', 'recover', 'reply'))) {
            $this->showErrorJson("专辑数据为空");
        }
        $feedback = new UserFeedback();
        $feedbackinfo = $feedback->getInfo($op_id);

        if ($op_name == 'delete') {
            $feedback->update(array('status' => 0), "`id`={$op_id}");
        } else if ($op_name == 'recover') {
            $feedback->update(array('status' => 1), "`id`={$op_id}");
        } else if ($op_name == 'reply') {
            $feedback->insert(array(
                'replyid' => $op_id,
                'uid'     => $feedbackinfo['uid'],
                'content' => $content,
                'status'  => 1,
                'addtime' => date('Y-m-d H:i:s')
            ));
        }
        return $this->showSuccJson('操作成功');

    }
}
new index();
?>