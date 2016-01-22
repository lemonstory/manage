<?php
include_once '../controller.php';

class savesameage extends controller
{
    public function action()
    {
        $action = $this->getRequest('action');
        $albumid = $this->getRequest('albumid');
        $agetype = $this->getRequest('agetype');
        $tagids = $this->getRequest('tagids');
        $recommenddesc = $this->getRequest('recommenddesc', "");
        if (empty($albumid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $albumobj = new Album();
        $albuminfo = $albumobj->get_album_info($albumid);
        if (empty($albuminfo)) {
            $this->showErrorJson(ErrorConf::albumInfoIsEmpty());
        }
        
        $manageobj = new ManageSystem();
        if (empty($action)) {
            // add
            $res = $manageobj->addRecommendSameAgeDb($albumid, $agetype);
        } else {
            // edit
            $data = array("agetype" => $agetype);
            $res = $manageobj->updateSameAgeInfoByIds($albumid, $data);
        }
        if ($res == false) {
            $this->showErrorJson($manageobj->getError());
        }
        
        if (!empty($tagids)) {
            $tagids = rtrim($tagids, ",");
            $tagids = explode(",", $tagids);
        }
        
        $managetagnewobj = new ManageTagNew();
        // 更新专辑下，所有标签的issameage=0
        $unrecommendres = $managetagnewobj->updateAlbumTagRelationUnRecommend($albumid, "issameage");
        if (empty($unrecommendres)) {
            $this->showErrorJson($managetagnewobj->getError());
        }
        
        // 更新专辑下，指定一级标签的issameage=1
        if (!empty($tagids)) {
            $recommendres = $managetagnewobj->updateAlbumTagRelationRecommend($albumid, $tagids, "issameage");
            if (empty($recommendres)) {
                $this->showErrorJson($managetagnewobj->getError());
            }
        }
        
        // 添加推荐语
        $recommenddescobj = new RecommendDesc();
        $recommenddescobj->addAlbumRecommendDescDb($albumid, $recommenddesc);
        
        $this->showSuccJson();
    }
}
new savesameage();
?>