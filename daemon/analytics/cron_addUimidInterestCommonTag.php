<?php
/*
 * 分析1小时内的行为日志记录，筛选出uimid感兴趣的标签
 */
include_once (dirname(dirname(__FILE__)) . "/DaemonBase.php");
class cron_addUimidInterestCommonTag extends DaemonBase {
    public $isWhile = false;
    protected function deal()
    {
        $endtime = time();
        $starttime = $endtime - 3600;
        
        $uimidinterestobj = new UimidInterest();
        $tagnewobj = new TagNew();
        $actionlogobj = new ActionLog();
        $actionloglist = $actionlogobj->getUserImsiActionLogListByTime($starttime, $endtime);
        
        $uimidalbumids = array();
        $uimidstoryids = array();
        $uimidsearchids = array();
        foreach ($actionloglist as $value) {
            $uimid = $value['uimid'];
            $actionid = $value['actionid'];
            $actiontype = $value['actiontype'];
            if ($actiontype == $actionlogobj->ACTION_TYPE_FAV_ALBUM) {
                $uimidalbumids[$uimid][] = $actionid;
            } elseif ($actiontype == $actionlogobj->ACTION_TYPE_DOWNLOAD_STORY) {
                $uimidstoryids[$uimid][] = $actionid;
            } elseif ($actiontype == $actionlogobj->ACTION_TYPE_LISTEN_STORY) {
                $uimidstoryids[$uimid][] = $actionid;
            } elseif ($actiontype == $actionlogobj->ACTION_TYPE_SEARCH_CONTENT) {
                $uimidsearchids[$uimid][] = $actionid;
            }
        }
        
        // 设备，感兴趣的专辑的标签
        if (!empty($uimidalbumids)) {
            foreach ($uimidalbumids as $uimid => $albumids) {
                if (!empty($albumids)) {
                    $albumids = array_unique($albumids);
                    $relationlist = $tagnewobj->getAlbumTagRelationListByAlbumIds($albumids);
                    if (!empty($relationlist)) {
                        foreach ($relationlist as $relationinfo) {
                            $tagid = $relationinfo['tagid'];
                            echo "uimid={$uimid}##tagid={$tagid}";
                            //$uimidinterestobj->updateUimidInterestTag($uimid, $tagid);
                        }
                    }
                }
                
            }
        }
        
        // 设备，感兴趣的搜索关键词的专辑标签
        if (!empty($uimidsearchids)) {
            
        }
        
        // 设备，感兴趣的故事的标签
        if (!empty($uimidstoryids)) {
            
        }
    }
    
    protected function checkLogPath() {
    }

}
new cron_addUimidInterestCommonTag();