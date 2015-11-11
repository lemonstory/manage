<?php
include_once '../controller.php';

class delfocus extends controller
{
    public function action()
    {
        $focusid = $this->getRequest('focusid', 0);
        if (empty($focusid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $managesysobj = new ManageSystem();
        $res = $managesysobj->delFocusDb($focusid);
        if (empty($res)) {
            $this->showErrorJson(array("code" => "101000", "desc" => "删除失败"));
        }
        
        $this->showSuccJson();
    }
}
new delfocus();