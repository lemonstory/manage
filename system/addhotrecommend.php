<?php
include_once '../controller.php';

class addfocus extends controller
{
	// @huqq delete
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
        $albumid = $this->getRequest('albumid');
        
        
        
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('hotrecommendside', "active");
        $smartyObj->display("system/addfocus.html"); 
    }
}
new addfocus();
?>