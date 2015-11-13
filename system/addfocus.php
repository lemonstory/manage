<?php
include_once '../controller.php';

class addfocus extends controller
{
    public function action()
    {
        $focusid = $this->getRequest('focusid');
        $focusinfo = array();
        $manageobj = new ManageSystem();
        if (!empty($focusid)) {
        	// edit
        	$focusinfo = $manageobj->getFocusInfo($focusid);
        	if (!empty($focusinfo)) {
        	    $aliossobj = new AliOss;
        	    $focusinfo['cover'] = $aliossobj->getFocusUrl($focusinfo['id'], $value['covertime'], 1);
        	}
        }
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign("focusinfo", $focusinfo);
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('focusside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("system/addfocus.html"); 
    }
}
new addfocus();
?>