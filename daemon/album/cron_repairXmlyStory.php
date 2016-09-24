<?php
/**
 *
 * 专辑里面的故事内容有重复的(域名不同,但是文件相同)
 * 查找每一个专辑的未删除的故事音频文件,如果出现重复(相同)的音频文件,旧的保留,新的删除
 *
 *
 */
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_repairXmlyStory extends DaemonBase {
    protected $processnum = 1;
    protected function deal() {

        $all = "all";
        $options = getopt("a:");

        $logfile = "/alidata1/cron_repairXmlyStory.log";
        $fp = @fopen($logfile, "a+");
        if(!empty($options)) {

            $optA = $options['a'];
        }

        $repair_album_id = intval($optA);
        if(0 == $repair_album_id && 0 != strcmp($optA,$all)) {

            die("Fail: -a param is incorrect");
        }

        $this->c_repair_xmly_story($repair_album_id,$fp);
        exit;
    }

    protected function checkLogPath() {}

    protected function c_repair_xmly_story($repair_album_id,$fp) {

        $album_obj = new Album();
        $story_obj = new Story();
        $p = 1;
        $per_page = 1;
        $repair_album_count = 0;
        $repair_story_count = 0;
        if($repair_album_id > 0) {
            $where = "`id` = {$repair_album_id} AND `from` = 'xmly'";
        }else {
            $where = "`from`='xmly'";
        }
        $order = 'order by `add_time` asc';

        while (true) {
            $limit = ($p - 1) * $per_page;
            $album_list = $album_obj->get_list($where, "{$limit},{$per_page}");
            if (!$album_list) {
                break;
            }
            foreach ($album_list as $k => $album) {

                $album_id = $album['id'];
                $story_where = "`album_id` = {$album_id}";
                $story_list = $story_obj->get_filed_list('*',$story_where,$order);
                $story_id_list = array();
                $story_source_audio_url = array();
                $del_story_id_list = array();
                foreach ($story_list as $k => $story) {
                    if(1 == $story['status']) {
                        $story_id_list[] = $story['id'];
                        $story_source_audio_url[$story['id']] = $story['source_audio_url'];
                    }
                }

                foreach($story_source_audio_url as $story_id => $source_audio_url) {

                    $source_audio_path_arr = parse_url($source_audio_url);
                    $path = $source_audio_path_arr['path'];

                    $time = 0;
                    foreach($story_source_audio_url as $story_id => $source_audio_url) {

                        $pos = strpos($source_audio_url,$path);
                        if($pos !== false) {
                            $time++;
                            if($time >1) {
                                $del_story_id_list[] = $story_id;
                                unset($story_source_audio_url[$story_id]);
                            }
                        }
                    }
                }
                
                if(count($del_story_id_list) > 0) {
                    $repair_album_count++;
                    foreach ($del_story_id_list as $story_id) {
                        $repair_story_count++;
                        $ret = $story_obj->update(array('status' => 0),"id={$story_id}");
                        $content = sprintf("[%d]%s 删除故事 %d ret=%d\r\n",$album['id'],$album['title'],$story_id,$ret);
                        echo $content;
                        fwrite($fp, $content);
                    }
                }

//                var_dump(count($del_story_id_list));
//                var_dump($del_story_id_list);
//                exit;

            }
            $p++;
        }
        $content = sprintf("修复专辑数: %d 修复故事数: %d\r\n",$repair_album_count,$repair_story_count);
        echo $content;
        fwrite($fp, $content);
        fclose($fp);
    }
}
new cron_repairXmlyStory();