<?php
include_once '../controller.php';

class delhotrecommend extends controller
{
    public function action()
    {
        $albumid = $this->getRequest('albumid', 0);
        if (empty($albumid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $managesysobj = new ManageSystem();
        $res = $managesysobj->delHotRecommendByAlbumId($albumid);
        if (empty($res)) {
            $this->showErrorJson(array("code" => "101000", "desc" => "删除失败"));
        }
        
        $this->showSuccJson();
    }
}
new delhotrecommend();