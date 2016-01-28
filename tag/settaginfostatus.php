<?php
include_once '../controller.php';

class settaginfostatus extends controller
{
    public function action()
    {
        $tagid = $this->getRequest('tagid', 0) + 0;
        $type = $this->getRequest("type", '');
        $status = $this->getRequest('status', 0);
        $ordernum = $this->getRequest('ordernum', 0);
        if (empty($tagid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        $updata = array();
        $settagdata = array();
        if ($type == 'status') {
            $updata['status'] = $status;
        }
        if ($type == 'ordernum') {
            $updata['ordernum'] = $ordernum;
        }
        
        if (!empty($updata)) {
            $tagnewobj = new TagNew();
            $taginfo = current($tagnewobj->getTagInfoByIds($tagid));
            if (empty($taginfo)) {
                $this->showErrorJson(ErrorConf::TagInfoIsEmpty());
            }
            
            $result = $tagnewobj->updateTagInfo($tagid, $taginfo['name'], $updata);
            if(empty($result)) {
                $this->showErrorJson($managesysobj->getError());
            }
        }
        
        // 标签下线后，album_tag_relation中，tagid=$tagid的推荐状态同样需要下线
        /* if ($status == 2) {
            $managetagnewobj = new ManageTagNew();
            $tagwhere = "`tagid` = '{$tagid}'";
            $settagdata = array("recommendstatus" => $status, "newonlinestatus" => $status, "sameagestatus" => $status);
            $managetagnewobj->updateAlbumTagRelationRecommendStatus($settagdata, $tagwhere);
        } */
        
        $this->showSuccJson();
    }
}
new settaginfostatus();