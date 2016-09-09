<?php
/**
 * Created by PhpStorm.
 * User: gaoyong
 * Date: 16/9/6
 * Time: 下午4:28
 */

//目前的故事年龄存储有两个age_type:1 0～2岁;2 3～6岁;3 7～10岁,age_str:6岁+,2-10岁
//更改为:age_str保留,min_age,max_age由age_str分析得到,便于前端根据年龄区间获取数据:例:11~14岁
//业务逻辑:
//      通过字符串分解age_str,得到min_age,max_age.然后更改album信息
//使用:
//  修复album_id为4422的故事辑年龄:
//  php your_path/cron_addAgeWithAgeType.php -a 10988
//
//  修复所有专辑[慎重]:
//      php your_path/cron_addAgeWithAgeType.php -a all

include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");

class cron_addAgeWithAgeType extends DaemonBase {

    protected $isWhile = false;

    protected function deal() {


        $all = "all";
        $options = getopt("a:");
        $condition = "";
        $limit = 1000;

        $logfile = "/alidata1/cron_addAgeWithAgeType.log";
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

            $album_id_list = $album_obj->get_list($where, $limit,"id");
            $album_age_str_list = $album_obj->get_list($where, $limit,"age_str");
            $album_min_age_list = $album_obj->get_list($where, $limit,"min_age");
            $album_max_age_list = $album_obj->get_list($where, $limit,"max_age");
            $album_count = count($album_id_list);

            if($album_count > 0) {

                $last_index = $album_count - 1;
                $sub_album_id = $album_id_list[$last_index];
                $id_condition = " and id > {$sub_album_id}";

                foreach ($album_id_list as $k=>$album_id) {

                    $count++;
                    $album_age_str = $album_age_str_list[$k];
                    $album_min_age = $album_min_age_list[$k];
                    $album_max_age = $album_max_age_list[$k];
                    if(!empty($album_age_str) && (empty($album_min_age) && empty($album_max_age))) {

//                        var_dump($album_id);
//                        var_dump($album_age_str);
                        $album_age_str_rep = str_replace("岁","",$album_age_str);
                        $album_age_str_rep = str_replace("+","-14",$album_age_str_rep);
                        $age_str_arr = explode("-",$album_age_str_rep);
                        if(count($age_str_arr) == 1) {
                            $album_min_age = $age_str_arr[0];
                            $album_max_age_arr = array(2,6,10,14);
                            foreach ($album_max_age_arr as $value) {
                                if($album_min_age < $value) {
                                    $album_max_age = $value;
                                    break;
                                }
                            }

                        }else if(count($age_str_arr) == 2) {
                            $album_min_age = $age_str_arr[0];
                            $album_max_age = $age_str_arr[1];
                        }
//                        var_dump($age_str_arr);

                        if(!empty($album_max_age)) {
                            $new_album_info['min_age'] = intval($album_min_age);
                            $new_album_info['max_age'] = intval($album_max_age);
                            $ret = $album_obj->update($new_album_info, "`id`={$album_id}");
                            if(!$ret) {
                                die("Fail: album info update");
                            }else {
                                $repair_num++;
                                fwrite($fp, "addAgeWithAgeType: album_age_str: {$album_age_str}, album_min_age: {$album_min_age}, album_max_age: {$album_max_age}\n");
                            }
                        }else {
                            die("Fail: album_min_age or album_max_age is empty");
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
new cron_addAgeWithAgeType ();