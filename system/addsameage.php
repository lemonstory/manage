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
        
        $aliossobj = new AliOss();
        if (!empty($albuminfo['cover'])) {
            $albuminfo['cover'] = $aliossobj->getImageUrlNg($aliossobj->IMAGE_TYPE_ALBUM, $albuminfo['cover'], 100, $albuminfo['cover_time']);
        }
        if (empty($action)) {
            $agetype = $albuminfo['age_type'];
        } else {
            $manageobj = new ManageSystem();
            $sameageinfo = $manageobj->getRecommendInfoByFilter("share_story", "recommend_same_age", "`albumid` = '{$albumid}'");
            $agetype = $sameageinfo['agetype'];
        }
        
        // 获取一级标签列表
        $tagnewobj = new TagNew();
        $firsttaglist = $tagnewobj->getFirstTagList(50);
        
        // 获取选中的标签列表
        $relationlist = current($tagnewobj->getAlbumTagRelationListByAlbumIds($albumid));
        
        $filterfirsttaglist = array();
        foreach ($firsttaglist as $firstvalue) {
            $firstvalue['checked'] = 0;
            foreach ($relationlist as $relationtagid => $relationvalue) {
                if ($relationtagid == $firstvalue['id']) {
                    $firstvalue['checked'] = 1;
                }
            }
            $filterfirsttaglist[] = $firstvalue;
        }
        
        // 获取推荐语
        $recommenddescobj = new RecommendDesc();
        $recommenddescinfo = current($recommenddescobj->getAlbumRecommendDescList($albumid));
        
        $refer = "";
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $refer = $_SERVER['HTTP_REFERER'];
        }
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('action', $action);
        $smartyObj->assign('albuminfo', $albuminfo);
        $smartyObj->assign('agetype', $agetype);
        $smartyObj->assign("agetypenamelist", $agetypenamelist);
        $smartyObj->assign("filterfirsttaglist", $filterfirsttaglist);
        $smartyObj->assign("recommenddescinfo", $recommenddescinfo);
        $smartyObj->assign('refer', $refer);
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('sameageside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("system/addsameage.html"); 
    }
}
new addsameage();
?>