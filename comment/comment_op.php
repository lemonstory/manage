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
		} else if ($op_name == 'edit_star_level') {
			$value = (int)$this->getRequest('value');
			if ($value < 0 || $value > 5) {
				return $this->showErrorJson(ErrorConf::CommentStarLevelIsError());
			}
			$comment->update(array('star_level' => $value), "`id`={$op_id}");
		} else if ($op_name == 'delete') {
			$comment->update(array('status' => 0), "`id`={$op_id}");
		} else if ($op_name == 'recover') {
			$comment->update(array('status' => 1), "`id`={$op_id}");
		}

		return $this->showSuccJson('操作成功');

    }
}
new comment_op();
?>