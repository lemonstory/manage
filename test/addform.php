<?php
include_once '../controller.php';

class addform extends controller
{
    public function action()
    {
        $smartyObj = $this->getSmartyObj();
        $smartyObj->display("test/addform.html");
    }
}
new addform();
?>