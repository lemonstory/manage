<?php
include_once '../controller.php';

class delsameage extends controller
{
    public function action()
    {
        $albumid = $this->getRequest('albumid', 0);
        if (empty($albumid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $managesysobj = new ManageSystem();
        $res = $managesysobj->delSameAgeByAlbumId($albumid);
        if (empty($res)) {
            $this->showErrorJson(array("code" => "101000", "desc" => "删除失败"));
        }
        
        // 取消专辑的推荐标签，更新issameage=0
        $managetagnewobj = new ManageTagNew();
        $managetagnewobj->updateAlbumTagRelationUnRecommend($albumid, "issameage");
        
        $this->showSuccJson();
    }
}
new delsameage();