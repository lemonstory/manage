<?php
include_once '../controller.php';

class table extends controller
{
    public function filters()
    {
        return array(
                'authLogin' => array(
                        'requireLogin' => false,
                ),
                'privilege' => array(
                        'checkPrivilege' => false,
                ),
        );
    }
    
    public function action()
    {
        $smartyObj = $this->getSmartyObj();
        $smartyObj->display("test/table.html");
    }
}
new table();
?>