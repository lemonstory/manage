<?php
/**
 *
 * 生成专辑创作者{作者包括:原著,翻译者,插画者,主播}
 * 专辑的创作者是专辑下故事的创作者的合
 * 每15分钟定时执行
 *
 * 1) 故事-创作者信息
 * 2) 专辑-创作者信息 <后台进程>
 * 3) 创作者-各个年龄段专辑数量 <后台进程>
 *
 */

include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");

class cron_genAlbumCreator extends DaemonBase
{

    protected $isWhile = false;
    protected $circulation_process = true;

    protected function deal()
    {
        $all = "all";
        $options = getopt("a:");

        $logfile = "/alidata1/cron_genAlbumCreator.log";
        $fp = @fopen($logfile, "a+");

        if (!empty($options)) {

            $optA = $options['a'];
        }

        $repair_album_id = intval($optA);
        if (0 == $repair_album_id && 0 != strcmp($optA, $all)) {

            die("Fail: -a param is incorrect");
        }

        $this->c_album_anthor();
    }

    public function c_album_anthor() {

        $album = new Album();
        $story = new Story();
        $p = 1;
        $per_page = 500;
        $limit = 0;
        $count = 0;
        $repair_num = 0;
        $not_repair_num = 0;

        while (true) {

            if ($this->circulation_process) {
                $limit = ($p - 1) * $per_page;
                $album_list = $album->get_list("`story_num` > 0 order by `id` asc", "{$limit},{$per_page}");
                //$album_list = $album->get_list("`id`=15144");
            }

            if (!$album_list) {
                break;
            }

            //遍历专辑
            foreach ($album_list as $k => $v) {

                $count++;
                $story_author_uid_arr = array();
                $story_translator_uid_arr = array();
                $story_illustrator_uid_arr = array();
                $story_anchor_uid_arr = array();
                //遍历专辑下的故事
                //获取每个故事的作者
                $album_id = $v['id'];
                $filed = "`author_uid`,`translator_uid`,`illustrator_uid`,`anchor_uid`";
                $story_where = "`album_id` = {$album_id} AND `status` = 1";
                $order = "order by `view_order`";
                $story_list = $story->get_filed_list($filed,$story_where,$order);
                foreach ($story_list as $sk => $story_item) {

                    $author_uid_item_arr = explode(",",$story_item['author_uid']);
                    foreach ($author_uid_item_arr as $author_uid) {
                        if(!in_array($author_uid,$story_author_uid_arr) && $author_uid) {
                            $story_author_uid_arr[] = $author_uid;
                        }
                    }

                    $translator_uid_item_arr = explode(",",$story_item['translator_uid']);
                    foreach ($translator_uid_item_arr as $translator_uid) {
                        if(!in_array($translator_uid,$story_translator_uid_arr) && !empty($translator_uid)) {
                            $story_translator_uid_arr[] = $translator_uid;
                        }
                    }

                    $illustrator_uid_item_arr = explode(",",$story_item['illustrator_uid']);
                    foreach ($illustrator_uid_item_arr as $illustrator_uid) {

                        if(!in_array($illustrator_uid,$story_illustrator_uid_arr) && !empty($illustrator_uid)) {
                            $story_illustrator_uid_arr[] = $illustrator_uid;
                        }
                    }

                    $anchor_uid_item_arr = explode(",",$story_item['anchor_uid']);
                    foreach ($anchor_uid_item_arr as $anchor_uid) {

                        if(!in_array($anchor_uid,$story_anchor_uid_arr) && !empty($anchor_uid)) {
                            $story_anchor_uid_arr[] = $anchor_uid;
                        }
                    }
                }

                //更新专辑的作者
                $data = array();
                $data['author_uid'] = empty($story_author_uid_arr) ? "" : implode(",",$story_author_uid_arr);
                $data['translator_uid'] = empty($story_translator_uid_arr) ? "" : implode(",",$story_translator_uid_arr);
                $data['illustrator_uid'] = empty($story_illustrator_uid_arr) ? "" : implode(",",$story_illustrator_uid_arr);
                $data['anchor_uid'] = empty($story_anchor_uid_arr) ? "" : implode(",",$story_anchor_uid_arr);
                $album_where = "`id` = {$album_id}";

                if(!empty($data['author_uid']) || !empty($data['translator_uid']) || !empty($data['illustrator_uid']) || !empty($data['anchor_uid'])) {
                    $repair_num++;
                    $ret = $album->update($data,$album_where);
                    $content = sprintf("[%s]专辑[%s] 作者[%s] 译者[%s] 插画[%s] 主播[%s] ret=%d\r\n", $v['id'],$v['title'],$data['author_uid'],$data['translator_uid'],$data['illustrator_uid'],$data['anchor_uid'],$ret);
                    echo $content;
                }else {
                    $not_repair_num++;
                    $content = sprintf("[%s]专辑[%s] 作者[%s] 译者[%s] 插画[%s] 主播[%s]均为空,不做处理\r\n", $v['id'],$v['title'],$data['author_uid'],$data['translator_uid'],$data['illustrator_uid'],$data['anchor_uid']);
                    echo $content;
                }
            }
            $p++;
            //sleep(1);
            //exit();
        }
        $content = sprintf("done! 处理:%d , 未处理:%d, 共计:%d 个专辑\r\n", $repair_num,$not_repair_num,$count);
        echo $content;
    }

    protected function checkLogPath()
    {
        
    }
}
new cron_genAlbumCreator ();