<?php
include_once '../controller.php';

class addnewonline extends controller
{
    public function action()
    {
        $action = $this->getRequest("action");
        $albumid = $this->getRequest('albumid');
        if (empty($albumid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        $albumobj = new Album();
        $albuminfo = $albumobj->get_album_info($albumid);
        if (empty($albuminfo)) {
            $this->showErrorJson(ErrorConf::albumInfoIsEmpty());
        }
        
        $configvarobj = new ConfigVar();
        $agetypenamelist = $configvarobj->AGE_TYPE_NAME_LIST;
        
        $aliossobj = new AliOss();
        if (!empty($albuminfo['cover'])) {
            $albuminfo['cover'] = $aliossobj->getImageUrlNg($aliossobj->IMAGE_TYPE_ALBUM, $albuminfo['cover'], 100, $albuminfo['cover_time']);
        }
        
        if (empty($action)) {
            $agetype = $albuminfo['age_type'];
        } else {
            $manageobj = new ManageSystem();
            $sameageinfo = $manageobj->getRecommendInfoByFilter("share_main", "recommend_same_age", "`albumid` = '{$albumid}'");
            $agetype = $sameageinfo['agetype'];
        }
        
        // 获取一级标签列表
        $tagnewobj = new TagNew();
        $firsttaglist = $tagnewobj->getFirstTagList(50);
        
        $refer = "";
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $refer = $_SERVER['HTTP_REFERER'];
        }
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('action', $action);
        $smartyObj->assign('albuminfo', $albuminfo);
        $smartyObj->assign('agetype', $agetype);
        $smartyObj->assign("agetypenamelist", $agetypenamelist);
        $smartyObj->assign("firsttaglist", $firsttaglist);
        $smartyObj->assign('refer', $refer);
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('newonlineside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("system/addnewonline.html"); 
    }
}
new addnewonline();
?>