<?php
include_once '../controller.php';

class album_add extends controller
{
    public function action()
    {
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('albumactive', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("album/album_edit.html"); 

    }
}
new album_add();
?>