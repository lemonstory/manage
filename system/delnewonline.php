<?php
include_once '../controller.php';

class delnewonline extends controller
{
    public function action()
    {
        $albumid = $this->getRequest('albumid', 0);
        if (empty($albumid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $managesysobj = new ManageSystem();
        $res = $managesysobj->delNewOnlineByAlbumId($albumid);
        if (empty($res)) {
            $this->showErrorJson(array("code" => "101000", "desc" => "删除失败"));
        }
        
        // 取消专辑的推荐标签，更新isnewonline=0
        $managetagnewobj = new ManageTagNew();
        $managetagnewobj->updateAlbumTagRelationUnRecommend($albumid, "isnewonline");
        
        $this->showSuccJson();
    }
}
new delnewonline();