<?php
/*
 * 分析1小时内的行为日志记录，筛选出uimid感兴趣的标签
 */
include_once (dirname(dirname(__FILE__)) . "/DaemonBase.php");
class cron_addUimidInterestCommonTag extends DaemonBase {
    public $isWhile = false;
    protected function deal()
    {
        $nowtime = time();
        $starttime = date("Y-m-d H:i:s", $nowtime - 3600);
        $endtime = date("Y-m-d H:i:s", $nowtime);
        
        $uimidinterestobj = new UimidInterest();
        $tagnewobj = new TagNew();
        $storyobj = new Story();
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
        
        // 设备，感兴趣的故事，所属的专辑
        if (!empty($uimidstoryids)) {
            foreach ($uimidstoryids as $uimid => $storyids) {
                $storyids = array_unique($storyids);
                $storyalbumids = array();
                if (!empty($storyids)) {
                    $storyidstr = implode(",", $storyids);
                    $storyalbumids = $storyobj->get_list("`id` IN ($storyidstr)", 5000, 'album_id');
                }
                
                // 获得专辑后，追加到设备感兴趣的专辑数组中
                if (!empty($storyalbumids)) {
                    $storyalbumids = array_unique($storyalbumids);
                    if (empty($uimidalbumids[$uimid])) {
                        $uimidalbumids[$uimid] = $storyalbumids;
                    } else {
                        $uimidalbumids[$uimid] = array_merge($uimidalbumids[$uimid], $storyalbumids);
                    }
                }
            }
        }
        
        
        // 记录设备，感兴趣的专辑的标签
        if (!empty($uimidalbumids)) {
            foreach ($uimidalbumids as $uimid => $albumids) {
                if (!empty($albumids)) {
                    $albumids = array_unique($albumids);
                    $relationlist = $tagnewobj->getAlbumTagRelationListByAlbumIds($albumids);
                    if (!empty($relationlist)) {
                        $relationlist = current($relationlist);
                        foreach ($relationlist as $relationinfo) {
                            $tagid = $relationinfo['tagid'];
                            $uimidinterestobj->updateUimidInterestTag($uimid, $tagid);
                        }
                    }
                }
                
            }
        }
        
        // 设备，感兴趣的搜索关键词的专辑标签
        if (!empty($uimidsearchids)) {
            
        }
        
        
    }
    
    protected function checkLogPath() {
    }

}
new cron_addUimidInterestCommonTag();