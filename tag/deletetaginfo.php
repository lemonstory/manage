<?php
/*
 * 删除标签
 */
include_once '../controller.php';
class deletetaginfo extends controller
{
    public function action()
    {
        $tagid = $this->getRequest('tagid', 0) + 0;
        if (empty($tagid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $tagnewobj = new TagNew();
        $taginfo = current($tagnewobj->getTagInfoByIds($tagid));
        if (empty($taginfo)) {
            $this->showErrorJson(ErrorConf::TagInfoIsEmpty());
        }
        
        // 删除标签
        $result = $tagnewobj->deleteTagInfo($tagid);
        if(empty($result)) {
            $this->showErrorJson(array("code" => "xx", "desc" => "删除标签失败"));
        }
        
        // 删除指定标签下，所有专辑标签关联记录
        $tagnewobj->deleteAlbumTagRelationByTagId($tagid);
        
        $this->showSuccJson();
    }
}
new deletetaginfo();