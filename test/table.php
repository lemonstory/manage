<?php
include_once '../controller.php';

class table extends controller
{
    public function action()
    {
        $smartyObj = $this->getSmartyObj();
        $smartyObj->display("test/table.html");
    }
}
new table();
?>