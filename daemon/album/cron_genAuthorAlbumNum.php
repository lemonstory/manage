<?php
/**
 * 根据作者与专辑的关系表,定时统计出作者的专辑数量
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
        $author_all_arr = $author->getAllAuthors();
        if(is_array($author_all_arr) && !empty($author_all_arr)) {

            $db = DbConnecter::connectMysql('share_story');
            foreach ($author_all_arr as $k => $author_item) {

                //$author_item['uid'] = 14940;
                $sql = "SELECT COUNT(*) as count FROM `album_author_relation` WHERE `author_uid`={$author_item['uid']}";
                $st = $db->query($sql);
                $r = $st->fetchAll();
                $count = intval($r[0]['count']);

                if($count > 0) {
                    $data = array();
                    $data['album_num'] = $count;
                    $where = "uid = {$author_item['uid']}";
                    $ret = $author->update($data,$where);
                    $content = sprintf("更新作者[%s] 专辑数量为 %d ret = %d\r\n", $author_item['uid'],$count,$ret);
                    echo $content;
                }else {
                    $content = sprintf("作者[%s] 专辑数量为 0\r\n", $author_item['uid']);
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