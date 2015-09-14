<?php
include_once '../controller.php';

class interfaces extends controller
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
        $smartyObj->display("test/interfaces.html");
    }
}
new interfaces();
?>