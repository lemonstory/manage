<?php
include_once '../controller.php';

class comment_op extends controller
{
    public function action()
    {
        $op_name = $this->getRequest('op_name', '');
        $op_id = $this->getRequest('op_id', 0);
        if (!$op_id) {
        	return $this->showErrorJson(ErrorConf::CommentContentIsEmpty());
        }
        $comment = new Comment();

		if ($op_name == 'edit_comment') {
			$value = $this->getRequest('value');
			$comment->update(array('content' => $value), "`id`={$op_id}");
		}

		return $this->showSuccJson('操作成功');

    }
}
new comment_op();
?>