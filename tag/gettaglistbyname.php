<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 16/9/20
 * Time: 下午9:22
 */
include_once '../controller.php';

class gettaglistbyname extends controller
{
    public function action()
    {
        $tagList = $where = '';
        
        $tagName   = $this->getRequest('tag_name', '');
        $callback   = $this->getRequest('callback', '');

        if ($tagName) {
            $where = " `name` like '%{$tagName}%' ";
        }

        $tagObj = new ManageTagNew();
        if($where){
            $tagList = $tagObj->getTagListByColumnSearch($where);
        }

        echo $callback.'('.json_encode($tagList).')';
    }
}
new gettaglistbyname();