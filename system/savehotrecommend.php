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
            // 先设置为下线状态,否则后面的标签推荐一直无法为上线状态
            $data = array('status'=>$manageobj->RECOMMEND_STATUS_OFFLINE);
            $res = $manageobj->updateHotRecommendInfoByIds($albumid,$data);
            //$res = true;
        }

        if ($res == false) {
            $this->showErrorJson($manageobj->getError());
        }
        
        if (!empty($tagids)) {
            $tagids = rtrim($tagids, ",");
            $tagids = explode(",", $tagids);
        }
        
        $managetagnewobj = new ManageTagNew();
        // 更新专辑下，所有标签的isrecommend=0
        $unrecommendres = $managetagnewobj->updateAlbumTagRelationUnRecommend($albumid, "isrecommend");
        if (empty($unrecommendres)) {
            $this->showErrorJson($managetagnewobj->getError());
        }
        
        // 更新专辑下，指定一级标签的isrecommend=1
        if (!empty($tagids)) {
            $recommendres = $managetagnewobj->updateAlbumTagRelationRecommend($albumid, $tagids, "isrecommend");
            if (empty($recommendres)) {
                $this->showErrorJson($managetagnewobj->getError());
            }
        }

        //清除故事专辑与标签及是否被推荐至{热门,同龄,最新}的cache
        $tagnewobj = new TagNew();
        $tagnewobj->clearAlbumTagRelationCacheByAlbumIds($albumid);


        // 添加或更新推荐语
        $recommenddescobj = new RecommendDesc();
        $recommenddescobj->addAlbumRecommendDescDb($albumid, $recommenddesc);
        
        $this->showSuccJson();
    }
}
new savehotrecommend();
?>