<?php
/**
 * Created by PhpStorm.
 * User: gaoyong
 * Date: 16/9/6
 * Time: 下午4:28
 */

//专辑增加上架状态,根据上架状态来确定专辑是否在前端展示,专辑默认为未上架状态
//
//业务逻辑:
//      将热门推荐,同龄在听,最新上线中,所有上架的专辑,专辑上架状态更改为上架.
//使用:
//  修复album_id为4422的故事辑年龄:
//  php your_path/cron_addOnlineStatus.php -a 10988
//
//  修复所有专辑[慎重]:
//      php your_path/cron_addOnlineStatus.php -a all

include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");

class cron_addOnlineStatus extends DaemonBase {

    protected $isWhile = false;

    protected function deal() {


        $all = "all";
        $options = getopt("a:");

        $logfile = "/alidata1/cron_addOnlineStatus.log";
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

        $recommend_obj = new Recommend();
        $album_obj = new Album();
        $currentpage = 1;
        $len = 50;
        $ret = false;
        do{
            $album_obj = new Album();
            //$list = $recommend_obj->getRecommendHotList($currentpage, $len);
            //$list = $recommend_obj->getNewOnlineList(0,$currentpage, $len);
            $list = $recommend_obj->getSameAgeListenList(0,$currentpage, $len);
            $count = count($list) ;
            $album_ids_array = array();
            if($count > 0) {

                foreach ($list as $key => $item) {

                    $album_id = $item['albumid'];
                    $album_ids_array[] = $album_id;
                }
                $album_ids_str = implode(",",$album_ids_array);
                //var_dump($album_ids_str);
                //exit;
                $new_album_info['online_status'] = 1;
                $ret = $album_obj->update($new_album_info, "`id` in ({$album_ids_str})");
                echo $count."-----".count($album_ids_array)."\r\n";
            }
            $currentpage++;
            unset($album_obj);
            sleep(1);
        } while ($ret && $count > 0);



        $album_obj = null;
        $recommend_obj = null;
        fwrite($fp, "Done! count:{$count}, repair_num:{$repair_num},  not_required_num:{$not_required_num}\n");
        fclose($fp);
    }


    private function updateOnlineStatus($list,&$album_obj,&$fp,&$repair_num,&$not_required_num,&$count) {

        $ret = false;
        $count = $count + count($list) ;
        if($count > 0) {

            foreach ($list as $key => $item) {

                $album_id = $item['albumid'];
                $new_album_info['online_status'] = 1;
                $ret = $album_obj->update($new_album_info, "`id`={$album_id}");
                if(!$ret) {
                    echo ("Fail: album info update");
                    return $ret;
                    break;
                }else {
                    $repair_num++;
                    fwrite($fp, "addAgeWithAgeType: album_id: {$album_id}, online_status: {$new_album_info['online_status']}\n");
                }
                //$album_obj = null;
            }
            $ret = true;
        }
        return $ret;
    }

    protected function checkLogPath() {}

}
new cron_addOnlineStatus ();