<?php
include_once '../controller.php';

class savetaginfo extends controller
{
    public function action()
    {
        $tagid = $this->getRequest("tagid");
        $refer = $this->getRequest("refer");
        $name = $this->getRequest("name");
        $pid = $this->getRequest("pid");
        $ordernum = $this->getRequest("ordernum");
        if (empty($name)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $tagnewobj = new TagNew();
        $taginfo = array();
        if (!empty($tagid)) {
            // edit
            $taginfo = current($tagnewobj->getTagInfoByIds($tagid));
            if (empty($taginfo)) {
                $this->showErrorJson(ErrorConf::TagInfoIsEmpty());
            }
            $tagid = $taginfo['id'];
            $tagname = $taginfo['name'];
        } else {
            // add
            $tagid = $tagnewobj->addTag($name, $pid);
            if (empty($tagid)) {
                $this->showErrorJson($tagnewobj->getError());
            }
            $tagname = $name;
        }
        
        $updatedata = array();
        $updatedata['name'] = $name;
        $updatedata['pid'] = $pid;
        $updatedata['ordernum'] = $ordernum;
        
        //if (empty($pid)) {
            // 封面处理
            if (!empty($_FILES['cover'])) {
                $uploadobj = new Upload();
                $path = $uploadobj->uploadTagImageByPost($_FILES['cover'], $tagid);
                if (!empty($path)) {
                    // 更新cover
                    $cover = str_replace("tag/", '', $path);
                    $updatedata['cover'] = $cover;
                    $updatedata['covertime'] = time();
                }
            }
        //}
        $tagnewobj->updateTagInfo($tagid, $tagname, $updatedata);
        $this->showSuccJson();
    }
}
new savetaginfo();
?>