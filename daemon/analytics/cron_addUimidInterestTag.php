<?php
/*
 * 分析1小时内的行为日志记录，筛选出uimid感兴趣的标签
 */
include_once (dirname(dirname(__FILE__)) . "/DaemonBase.php");
class cron_addUimidInterestTag extends DaemonBase {
    public $isWhile = false;
    protected function deal()
    {
        $nowtime = time();
        $starttime = date("Y-m-d H:i:s", $nowtime - 3600);
        $endtime = date("Y-m-d H:i:s", $nowtime);
        
        $storyobj = new Story();
        $actionlogobj = new ActionLog();
        $actionloglist = $actionlogobj->getUserImsiActionLogListByTime($starttime, $endtime);
        
        $uimidfavalbumids = array();
        $uimiddownloadalbumids = array();
        $uimidlistenalbumids = array();
        
        $uimidstoryids = array();
        $uimiddownloadstoryids = array();
        $uimidlistenstoryids = array();
        
        $uimidsearchids = array();
        foreach ($actionloglist as $value) {
            $uimid = $value['uimid'];
            $actionid = $value['actionid'];
            $actiontype = $value['actiontype'];
            if ($actiontype == $actionlogobj->ACTION_TYPE_FAV_ALBUM) {
                $uimidfavalbumids[$uimid][] = $actionid;
            } elseif ($actiontype == $actionlogobj->ACTION_TYPE_DOWNLOAD_STORY) {
                $uimiddownloadstoryids[$uimid][] = $actionid;
                $uimidstoryids[$uimid][] = $actionid;
            } elseif ($actiontype == $actionlogobj->ACTION_TYPE_LISTEN_STORY) {
                $uimidlistenstoryids[$uimid][] = $actionid;
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
                    foreach ($storyalbumids as $storyalbumid) {
                        if (in_array($storyalbumid, $uimiddownloadstoryids[$uimid])) {
                            // 下载故事行为的专辑
                            $uimiddownloadalbumids[$uimid][] = $storyalbumid;
                        } elseif (in_array($storyalbumid, $uimiddownloadstoryids[$uimid])) {
                            // 收听故事行为的专辑
                            $uimidlistenalbumids[$uimid][] = $storyalbumid;
                        }
                    }
                }
            }
        }
        
        // 设备，感兴趣的搜索关键词的专辑
        if (!empty($uimidsearchids)) {
            
        }
        
        
        // 记录设备，感兴趣的专辑的标签
        $this->setUimidInterestTag($uimidfavalbumids, 2); // 收藏行为喜好度+2
        $this->setUimidInterestTag($uimiddownloadalbumids, 2); // 下载行为喜好度+2
        $this->setUimidInterestTag($uimidlistenalbumids, 1); // 收听行为喜好度+1
    }
    
    protected function checkLogPath() {
    }
    
    // 记录设备，感兴趣的专辑的标签，以及喜好度
    private function setUimidInterestTag($uimidalbumids, $incrnum)
    {
        if (!empty($uimidalbumids) || empty($incrnum)) {
            return false;
        }
        $logfile = "/alidata1/rc.log";
        $fp = @fopen($logfile, 'a+');
        
        $uimidinterestobj = new UimidInterest();
        $tagnewobj = new TagNew();
        foreach ($uimidalbumids as $uimid => $albumids) {
            if (!empty($albumids)) {
                continue;
            }
            $albumids = array_unique($albumids);
            $relationlist = $tagnewobj->getAlbumTagRelationListByAlbumIds($albumids);
            if (!empty($relationlist)) {
                $relationlist = current($relationlist);
                foreach ($relationlist as $relationinfo) {
                    $tagid = $relationinfo['tagid'];
                    $uimidinterestobj->updateUimidInterestTag($uimid, $tagid, $incrnum);
                    @fwrite($fp, "uimid={$uimid}##tagid={$tagid}##incrnum={$incrnum}");
                }
            }
        }
        fclose($fp);
        return true;
    }

}
new cron_addUimidInterestTag();