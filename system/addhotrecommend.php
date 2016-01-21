<?php
include_once '../controller.php';

class addhotrecommend extends controller
{
    public function action()
    {
        $albumid = $this->getRequest('albumid');
        if (empty($albumid)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        $albumobj = new Album();
        $albuminfo = $albumobj->get_album_info($albumid);
        if (empty($albuminfo)) {
            $this->showErrorJson(ErrorConf::albumInfoIsEmpty());
        }
        
        $aliossobj = new AliOss();
        if (!empty($albuminfo['cover'])) {
            $albuminfo['cover'] = $aliossobj->getImageUrlNg($aliossobj->IMAGE_TYPE_ALBUM, $albuminfo['cover'], 100, $albuminfo['cover_time']);
        }
        
        // 获取一级标签列表
        $tagnewobj = new TagNew();
        $firsttaglist = $tagnewobj->getFirstTagList(50);
        
        // 获取选中的标签列表
        $albumtaglist = current($tagnewobj->getAlbumTagRelationListByAlbumIds($albumid));
        
        // 获取推荐语
        $recommenddescobj = new RecommendDesc();
        $recommenddescinfo = current($recommenddescobj->getAlbumRecommendDescList($albumid));
        
        $refer = "";
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $refer = $_SERVER['HTTP_REFERER'];
        }
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('albuminfo', $albuminfo);
        $smartyObj->assign("firsttaglist", $firsttaglist);
        $smartyObj->assign("albumtaglist", $albumtaglist);
        $smartyObj->assign("recommenddescinfo", $recommenddescinfo);
        $smartyObj->assign('refer', $refer);
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('hotrecommendside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("system/addhotrecommend.html"); 
    }
}
new addhotrecommend();
?>