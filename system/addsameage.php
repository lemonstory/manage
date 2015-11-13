<?php
include_once '../controller.php';

class addsameage extends controller
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
        
        $aliobj = new AliOss();
        $albuminfo['cover'] = $aliobj->getImageUrlNg($albuminfo['cover'], 100, $albuminfo['cover_time']);
        if (empty($action)) {
            $agetype = $albuminfo['age_type'];
        } else {
            $manageobj = new ManageSystem();
            $sameageinfo = $manageobj->getRecommendInfoByFilter("share_main", "recommend_same_age", "`albumid` = '{$albumid}'");
            $agetype = $sameageinfo['agetype'];
        }
        
        $refer = "";
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $refer = $_SERVER['HTTP_REFERER'];
        }
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('action', $action);
        $smartyObj->assign('albuminfo', $albuminfo);
        $smartyObj->assign('agetype', $agetype);
        $smartyObj->assign("agetypenamelist", $agetypenamelist);
        $smartyObj->assign('refer', $refer);
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('sameageside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("system/addsameage.html"); 
    }
}
new addsameage();
?>