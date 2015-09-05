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
        $focusid = $this->getRequest('focusid');
        if (!empty($focusid)) {
        	// edit
        }
        
        
        
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('focusside', "active");
        $smartyObj->display("system/addfocus.html"); 
    }
}
new addfocus();
?>