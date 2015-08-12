<?php
include_once '../controller.php';

class stats extends controller
{
    public function action()
    {
        $smartyObj = $this->getSmartyObj();
        $smartyObj->display("test/stats.html");
    }
}
new stats();
?>