<?php
// 删除专辑的某个标签
include_once '../controller.php';
class deletealbumtagrelation extends controller
{
    public function action()
    {
        $albumid = $this->getRequest("albumid");
        $tagid = $this->getRequest("tagid");
        if (empty($albumid) || empty($tagid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $tagnewobj = new TagNew();
        $res = $tagnewobj->deleteAlbumTag($albumid, $tagid);
        if (empty($res)) {
            $this->showErrorJson(array("code" => "xx", "desc" => "删除标签失败"));
        }
        $this->showSuccJson();
    }
}
new deletealbumtagrelation();
?>