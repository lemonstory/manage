<?php
/**
 * Created by PhpStorm.
 * User: gaoyong
 * Date: 16/2/1
 * Time: 下午4:28
 */

//目前有的故事辑没有简介
//修复故事辑简介.
//业务逻辑:
//      检查每一个故事专辑的简介,当发现为空时:
//      读取故事专辑下第一个故事的简介.当故事的简介不为空时设置为故事辑的简介
//使用:
//  修复album_id为4422的故事辑简介:
//  php your_path/cron_repairAlbumIntro.php -a 10988
//
//  修复所有专辑[慎重]:
//      php your_path/cron_repairAlbumIntro.php -a all
//TODO:是否需要定期执行?

include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");

class cron_repairAlbumIntro extends DaemonBase {

    protected $isWhile = false;

    protected function deal() {


        $all = "all";
        $options = getopt("a:");
        $condition = "";
        $limit = 1000;

        $logfile = "/alidata1/cron_repairAlbumIntro.log";
        $fp = @fopen($logfile, "a+");
        $repair_num = 0;
        $not_required_num = 0;
        $count = 0;

        if(!empty($options)) {

            $optA = $options['a'];
        }

        $repair_album_id = intval($optA);
        if(0 == $repair_album_id && 0 != strcmp($optA,$all)) {

            die("Fail: -a param is incorrect");

        }

        if($repair_album_id > 0) {

            $condition .= " `id` = {$repair_album_id}";

        } else {

            $condition .= " 1 = 1 ";
        }

        $is_first_loop = true;
        $album_count = 0;
        $id_condition = "";
        $album_obj = new Album();

        while($is_first_loop || $album_count > 0) {

            $where = $condition.$id_condition;
            $is_first_loop = false;

            //TODO:可优化
            $album_id_list = $album_obj->get_list($where, $limit,"id");
            $album_intro_list = $album_obj->get_list($where, $limit,"intro");
            $album_count = count($album_id_list);

            if($album_count > 0) {

                $last_index = $album_count - 1;
                $sub_album_id = $album_id_list[$last_index];
                $id_condition = " and id > {$sub_album_id}";

                foreach ($album_id_list as $k=>$album_id) {

                    $count++;
                    $album_intro = $album_intro_list[$k];
                    if(empty($album_intro)) {

                        $story_obj = new Story();
                        $storys = $story_obj->getStoryList($album_id,"down",0,1,0);
                        $story_intro = $storys[0]['intro'];
                        if(!empty($story_intro)) {

                            $repair_num++;
                            $data = array('intro'=>$story_intro);
                            $album_obj->update($data, "`id` = {$album_id}");
                            fwrite($fp, "repairAlbumIntro: id: {$album_id}, intro: {$story_intro}\n");
                        }else {


                            $not_required_num++;
                            fwrite($fp, "doNothing: id: {$album_id}\n");
                        }
                    }
                }
            }
        }

        $album_obj = null;
        $story_obj = null;
        fwrite($fp, "Done! count:{$count}, repair_num:{$repair_num},  not_required_num:{$not_required_num}\n");
        fclose($fp);
    }

    protected function checkLogPath() {}

}
new cron_repairAlbumIntro ();