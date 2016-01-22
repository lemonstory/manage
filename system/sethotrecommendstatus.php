<?php
include_once '../controller.php';

class sethotrecommendstatus extends controller
{
    public function action()
    {
        $albumid = $this->getRequest('albumid', 0) + 0;
        $type = $this->getRequest("type", '');
        $status = $this->getRequest('status', 0);
        $ordernum = $this->getRequest('ordernum', 0);
        if (empty($albumid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        $updata = array();
        $settagdata = array();
        if ($type == 'status') {
            $updata['status'] = $status;
            $settagdata['recommendstatus'] = $status;
        }
        if ($type == 'ordernum') {
            $updata['ordernum'] = $ordernum;
        }
        
        if (!empty($updata)) {
            $albumobj = new Album();
            $albuminfo = $albumobj->get_album_info($albumid);
            if (empty($albuminfo)) {
                $this->showErrorJson(ErrorConf::albumInfoIsEmpty());
            }
            
            $managesysobj = new ManageSystem();
            $result = $managesysobj->updateHotRecommendInfoByIds($albumid, $updata);
            if(empty($result)) {
                $this->showErrorJson($managesysobj->getError());
            }
        }
        
        // 更新album_tag_relation的推荐status
        if (!empty($settagdata)) {
            $managetagnewobj = new ManageTagNew();
            $tagwhere = "`albumid` = '{$albumid}' and `isrecommend` = 1";
            $managetagnewobj->updateAlbumTagRelationRecommendStatus($settagdata, $tagwhere);
        }
        
        $this->showSuccJson();
    }
}
new sethotrecommendstatus();