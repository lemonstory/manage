<?php
include_once '../controller.php';

class savefocus extends controller
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
        var_dump($_FILES);
        $focusid = $this->getRequest("focusid");
        if (empty($focusid)) {
        	// add
        	
        } else {
        	// edit
        }
        $uploadobj = new Upload();
        $res = $uploadobj->uploadFocusImage($_FILES['fileupload'], $focusid);
        
    }
}
new savefocus();
?>