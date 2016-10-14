<?php
/**
 * 根据专辑的推荐数据定时:
 *
 *      1) 统计出各推荐位的专辑数量
 *      2) 统计出各推荐位的专辑所属年龄段的数量
 *
 * 后台进程,定时执行
 */

include_once(dirname(dirname(__FILE__)) . "/DaemonBase.php");

class cron_genRecommendAlbumNum extends DaemonBase
{

    protected $isWhile = false;
    protected $circulation_process = true;

    protected function deal()
    {
        $all = "all";
        $options = getopt("a:");

        $logfile = "/alidata1/cron_genRecommendAlbumNum.log";
        $fp = @fopen($logfile, "a+");

        if (!empty($options)) {

            $optA = $options['a'];
        }

        $repair_album_id = intval($optA);
        if (0 == $repair_album_id && 0 != strcmp($optA, $all)) {

            die("Fail: -a param is incorrect");
        }
        $this->cRecommendAlbumNum();
    }

    protected function cRecommendAlbumNum()
    {

        $recommend = new Recommend();
        $album = new Album();
        $configVar = new ConfigVar();
        $len = 20;

        $keyArr = array("hot","online","same_age");
        foreach ($keyArr as $KEY) {


            $currentPage = 1;
            $mergeAgeLevelAlbumNum = array();
            $recommendRes = array();
            do {
                switch ($KEY) {
                    //编辑推荐
                    case "hot":
                        $recommendRes = $recommend->getRecommendHotList($configVar->MIN_AGE,$configVar->MAX_AGE,$currentPage,$len);
                        break;
                    //最新上架
                    case "online":
                        $recommendRes = $recommend->getNewOnlineList($configVar->MIN_AGE,$configVar->MAX_AGE,$currentPage,$len);
                        break;
                    //同龄在听
                    case "same_age":
                        $recommendRes = $recommend->getSameAgeListenList($configVar->MIN_AGE,$configVar->MAX_AGE,$currentPage,$len);
                        break;

                }

                if (!empty($recommendRes)) {
                    $currentPage = $currentPage + 1;
                    $albumIds = array();
                    foreach ($recommendRes as $item) {
                        $albumIds[] = $item['id'];
                    }

                    if (!empty($albumIds)) {
                        $albumIdsStr = implode(",", $albumIds);
                        $db = DbConnecter::connectMysql($this->STORY_DB_INSTANCE);
                        $where = " `id` in ({$albumIdsStr})";
                        $sql = "SELECT `id`,`min_age`,`max_age` FROM `album` WHERE {$where}";
                        $st = $db->query($sql);
                        $albums_arr = $st->fetchAll();
                        if (is_array($albums_arr) && !empty($albums_arr)) {
                            $ageLevelAlbumNum = $album->getAgeLevelWithAlbums($albums_arr);
                            $mergeAgeLevelAlbumNum = $this->mergeAgeLevelAlbumNum($mergeAgeLevelAlbumNum, $ageLevelAlbumNum);
                        }
                    }
                }

            } while (!empty($recommendRes));

            if(is_array($mergeAgeLevelAlbumNum) && !empty($mergeAgeLevelAlbumNum)) {

                //排序
                foreach ($mergeAgeLevelAlbumNum["items"] as $key => $row)
                {
                    $min_age[$key]  = $row['min_age'];
                    $max_age[$key] = $row['max_age'];
                }
                array_multisort($min_age,SORT_ASC,$max_age,SORT_DESC,$mergeAgeLevelAlbumNum["items"]);
                $mergeAgeLevelAlbumNum = json_encode($mergeAgeLevelAlbumNum);
                //更新推荐专辑年龄段数量
                $db = DbConnecter::connectMysql($this->STORY_DB_INSTANCE);
                $sql = "REPLACE INTO `recommend_age_level`(`name`, `age_level_album_num`) values('{$KEY}', '{$mergeAgeLevelAlbumNum}');";
                var_dump($sql);
                $st = $db->query($sql);
                $errorCode = $st->errorCode();
                if($errorCode !== '00000') {
                    echo $st->errorInfo();
                }else{
                    echo "{$KEY} Done!\r\n";
                }
            }
        }
    }


    protected function mergeAgeLevelAlbumNum($merge, $item)
    {
        if (is_array($merge) && !empty($merge)) {
            foreach ($item['items'] as $k => $value) {

                $isItemExist = false;
                foreach ($merge['items'] as $mergeKey => $mergeItem) {
                    if ($value['min_age'] == $mergeItem['min_age'] && $value['max_age'] == $mergeItem['max_age']) {
                        $isItemExist = true;
                        $merge['items'][$mergeKey]['album_num'] = $mergeItem['album_num'] + $value['album_num'];
                    }
                }

                if(!$isItemExist) {
                    $merge['items'][] = $value;
                }
            }
            $merge['total'] = count($merge['items']);
        } else {
            $merge = $item;
        }
        return $merge;
    }

    protected function checkLogPath()
    {

    }

}
new cron_genRecommendAlbumNum();