<?php
include_once '../controller.php';

class savehotrecommend extends controller
{
    public function action()
    {
        $action = $this->getRequest('action');
        $albumid = $this->getRequest('albumid');
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
            $res = $manageobj->addRecommendHotDb($albumid);
        } else {
            // edit
            $res = true;
        }
        if ($res == false) {
            $this->showErrorJson($manageobj->getError());
        }
        
        if (!empty($tagids)) {
            $tagids = rtrim($tagids, ",");
            $tagids = explode(",", $tagids);
        }
        
        // 获取专辑原tagids
        $tagnewobj = new TagNew();
        $oldtaglist = current($tagnewobj->getAlbumTagRelationListByAlbumIds($albumid));
        $oldtagids = array();
        if (!empty($oldtaglist)) {
            $oldtagids = array_keys($oldtaglist);
        }
        // 对比得出新增、取消推荐的tagids
        $addtagids = array_diff($tagids, $oldtagids);
        // @huqq
        //$deltagids = array_diff($oldtagids, $tagids);
        
        $managetagnewobj = new ManageTagNew();
        // 更新专辑下，所有标签的isrecommend=1
        if (!empty($addtagids)) {
            $recommendres = $managetagnewobj->updateAlbumTagRelationRecommend($albumid, $addtagids, "isrecommend");
            if (empty($recommendres)) {
                $this->showErrorJson($managetagnewobj->getError());
            }
        }
        // 更新专辑下，所有标签的isrecommend=0
        if (!empty($deltagids)) {
            $unrecommendres = $managetagnewobj->updateAlbumTagRelationUnRecommend($albumid, $deltagids, "isrecommend");
            if (empty($unrecommendres)) {
                $this->showErrorJson($managetagnewobj->getError());
            }
        }
        
        // 添加或更新推荐语
        $recommenddescobj = new RecommendDesc();
        $recommenddescobj->addAlbumRecommendDescDb($albumid, $recommenddesc);
        
        $this->showSuccJson();
    }
}
new savehotrecommend();
?>