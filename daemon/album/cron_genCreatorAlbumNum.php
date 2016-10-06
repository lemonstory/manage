<?php
/**
 * 根据专辑的创作者数据定时:
 *
 *      1) 统计出创作者的专辑数量
 *      2) 统计出创作者专辑所属年龄段的数量
 */

include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");

class cron_genCreatorAlbumNum extends DaemonBase
{

    protected $isWhile = false;
    protected $circulation_process = true;

    protected function deal()
    {
        $all = "all";
        $options = getopt("a:");

        $logfile = "/alidata1/cron_genCreatorAlbumNum.log";
        $fp = @fopen($logfile, "a+");

        if (!empty($options)) {

            $optA = $options['a'];
        }

        $repair_album_id = intval($optA);
        if (0 == $repair_album_id && 0 != strcmp($optA, $all)) {

            die("Fail: -a param is incorrect");
        }
        $this->c_creator_album_num();
    }

    protected function c_creator_album_num() {

        //根据作者与专辑的关系表,定时统计作者的专辑数量
        $creator = new Creator();
        $album = new Album();
        $creator_all_arr = $creator->get_list("1 = 1","","uid","");

        if(is_array($creator_all_arr) && !empty($creator_all_arr)) {

            $db = DbConnecter::connectMysql('share_story');
            foreach ($creator_all_arr as $k => $creator_item_uid) {

                //$creator_item_uid = 14992;
                $where = " ( FIND_IN_SET({$creator_item_uid},`author_uid`) OR FIND_IN_SET({$creator_item_uid},`translator_uid`) OR FIND_IN_SET({$creator_item_uid},`illustrator_uid`) OR FIND_IN_SET({$creator_item_uid},`anchor_uid`) ) AND `online_status` = 1 AND `status` = 1";
                $sql = "SELECT `id`,`min_age`,`max_age` FROM `album` WHERE {$where}";
                //var_dump($sql);
                $st = $db->query($sql);
                $albums_arr = $st->fetchAll();
                if(is_array($albums_arr) && !empty($albums_arr)) {

                    $count = count($albums_arr);
                    $data = array();
                    $data['album_num'] = $count;
                    $data['age_level_album_num'] = json_encode($album->getAgeLevelWithAlbums($albums_arr));
                    $where = "uid = {$creator_item_uid}";
                    $ret = $creator->update($data,$where);
                    $content = sprintf("更新创作者[%s] 专辑数量为 %d ret = %d\r\n", $creator_item_uid,$count,$ret);
                    echo $content;
                } else {
                    $content = sprintf("创作者[%s] 专辑数量为 0\r\n", $creator_item_uid);
                    echo $content;
                }
                //exit;
            }
        }
    }


    protected function checkLogPath()
    {

    }

}

new cron_genCreatorAlbumNum();