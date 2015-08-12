<?php
include_once '../controller.php';

class index extends controller
{
    public function action()
    {
        $smartyObj = $this->getSmartyObj();
        $smartyObj->display("test/index.html");
    }
}
new index();
?>