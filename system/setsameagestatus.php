<?php
include_once '../controller.php';

class setsameagestatus extends controller
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
            $settagdata['sameagestatus'] = $status;
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
            
            $manageobj = new ManageSystem();
            $result = $manageobj->updateSameAgeInfoByIds($albumid, $updata);
            if(empty($result)) {
                $this->showErrorJson($manageobj->getError());
            }
        }
        
        // 更新album_tag_relation的推荐status
        if (!empty($settagdata)) {
            $managetagnewobj = new ManageTagNew();
            $tagwhere = "`albumid` = '{$albumid}' and `issameage` = 1";
            $managetagnewobj->updateAlbumTagRelationRecommendStatus($settagdata, $tagwhere);
        }
        
        $this->showSuccJson();
    }
}
new setsameagestatus();