<?php
include_once '../controller.php';

class button extends controller
{
    public function action()
    {
        $smartyObj = $this->getSmartyObj();
        $smartyObj->display("test/button.html");
    }
}
new button();
?>