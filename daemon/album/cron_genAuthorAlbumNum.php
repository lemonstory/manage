<?php
/**
 * 根据专辑的作者数据定时:
 *
 *      1) 统计出作者的专辑数量
 *      2) 统计出作者专辑所属年龄段的数量
 */

include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");

class cron_genAuthorAlbumNum extends DaemonBase
{

    protected $isWhile = false;
    protected $circulation_process = true;

    protected function deal()
    {
        $all = "all";
        $options = getopt("a:");

        $logfile = "/alidata1/cron_genAuthorAlbumNum.log";
        $fp = @fopen($logfile, "a+");

        if (!empty($options)) {

            $optA = $options['a'];
        }

        $repair_album_id = intval($optA);
        if (0 == $repair_album_id && 0 != strcmp($optA, $all)) {

            die("Fail: -a param is incorrect");
        }
        $this->c_anthor_album_num();
    }

    protected function c_anthor_album_num() {

        //根据作者与专辑的关系表,定时统计作者的专辑数量
        $author = new Author();
        $album = new Album();
        $author_all_arr = $author->get_list("1 = 1","","uid","");

        if(is_array($author_all_arr) && !empty($author_all_arr)) {

            $db = DbConnecter::connectMysql('share_story');
            foreach ($author_all_arr as $k => $author_item_uid) {

                //$author_item_uid = 14940;
                $where = " ( FIND_IN_SET({$author_item_uid},`author_uid`) OR FIND_IN_SET({$author_item_uid},`translator_uid`) OR FIND_IN_SET({$author_item_uid},`illustrator_uid`) ) AND `online_status` = 1 AND `status` = 1";
                $sql = "SELECT `id`,`min_age`,`max_age` FROM `album` WHERE {$where}";
                $st = $db->query($sql);
                $albums_arr = $st->fetchAll();
                if(is_array($albums_arr) && !empty($albums_arr)) {

                    $count = count($albums_arr);
                    $data = array();
                    $data['album_num'] = $count;
                    $data['age_level_album_num'] = json_encode($album->getAgeLevelWithAlbums($albums_arr));
                    $where = "uid = {$author_item_uid}";
                    $ret = $author->update($data,$where);
                    $content = sprintf("更新作者[%s] 专辑数量为 %d ret = %d\r\n", $author_item_uid,$count,$ret);
                    echo $content;
                } else {
                    $content = sprintf("作者[%s] 专辑数量为 0\r\n", $author_item_uid);
                    echo $content;
                }
            }
        }
    }


    protected function checkLogPath()
    {

    }

}

new cron_genAuthorAlbumNum();