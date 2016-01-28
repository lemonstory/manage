<?php
include_once '../controller.php';

class addtaginfo extends controller
{
    public function action()
    {
        $action = $this->getRequest("action");
        $tagnewobj = new TagNew();
        $taginfo = array();
        
        // 获取一级分类
        $firsttaglist = $tagnewobj->getFirstTagList(100);
        if (empty($action)) {
            // add
        } else {
            // edit
            $tagid = $this->getRequest('tagid');
            if (!empty($tagid)) {
                $taginfo = current($tagnewobj->getTagInfoByIds($tagid));
                if (!empty($taginfo['cover'])) {
                    $aliossobj = new AliOss();
                    $taginfo['cover'] = $aliossobj->getImageUrlNg("tag", $taginfo['cover'], 0, $taginfo['covertime']);
                }
            }
        }
        
        
        $refer = "";
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $refer = $_SERVER['HTTP_REFERER'];
        }
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('action', $action);
        $smartyObj->assign('tagid', $tagid);
        $smartyObj->assign('firsttaglist', $firsttaglist);
        $smartyObj->assign('taginfo', $taginfo);
        $smartyObj->assign('refer', $refer);
        $smartyObj->assign('tagactive', "active");
        $smartyObj->assign('addtaginfoside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("tag/addtaginfo.html"); 
    }
}
new addtaginfo();
?>