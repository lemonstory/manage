<?php
// 添加专辑标签
include_once '../controller.php';
class addalbumtagrelation extends controller
{
    public function action()
    {
        $albumid = $this->getRequest("albumid");
        $tagidstr = $this->getRequest("tagidstr");
        if (empty($albumid) || empty($tagidstr)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        $tagidstr = rtrim($tagidstr, ",");
        $tagids = explode(",", $tagidstr);
        
        $tagnewobj = new TagNew();
        $taglist = $tagnewobj->getTagInfoByIds($tagids);
        if (empty($taglist)) {
            $this->showErrorJson(ErrorConf::TagInfoIsEmpty());
        }
        
        foreach ($taglist as $taginfo) {
            $tagnewobj->addAlbumTagRelationInfo($albumid, $taginfo['id']);
        }
        $this->showSuccJson();
    }
}
new addalbumtagrelation();
?>