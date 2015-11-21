<?php
include_once '../controller.php';

class delrankuserlisten extends controller
{
    public function action()
    {
        $uid = $this->getRequest('uid', 0);
        if (empty($uid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $managesysobj = new ManageSystem();
        $res = $managesysobj->delRankListenUser($uid);
        if (empty($res)) {
            $this->showErrorJson(array("code" => "101000", "desc" => "删除失败"));
        }
        
        $this->showSuccJson();
    }
}
new delrankuserlisten();