<?php
include_once '../controller.php';

class login extends controller
{
    public function action()
    {
        $smartyObj = $this->getSmartyObj();
        $smartyObj->display("test/login.html");
    }
}
new login();
?>