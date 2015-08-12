<?php
include_once '../controller.php';

class interfaces extends controller
{
    public function action()
    {
        $smartyObj = $this->getSmartyObj();
        $smartyObj->display("test/interfaces.html");
    }
}
new interfaces();
?>