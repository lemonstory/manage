<?php

//修复故事辑内故事的排序.
//业务逻辑:
//  根据故事名修复故事排序值为0(view_order=0),或10000(view_order=10000)的故事排序指
//  备注:部分view_order=10000是编辑手写的,所有的view_order=0都是系统默认值
//使用:
//  修复album_id为4422的所有故事,序号取前3位数:
//  php your_path/cron_updateStoryViewOrder.php -a 4422 -l 3
//
//  修复所有专辑[慎重]:
//      php your_path/cron_updateStoryViewOrder.php -a all

include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");
class cron_updateStoryViewOrder extends DaemonBase
{
    protected $processnum = 1;
    protected $isWhile = false;

    protected function deal() {

        set_time_limit(0);
        $all = "all";
        $options = getopt("a:l:");
        $condition = "(`view_order`=0 or `view_order`=10000) ";
        $orderby = "order by `id` ASC";
        $limit = 1000;

        $logfile = "/alidata1/cron_updateStoryViewOrder.log";
        $fp = @fopen($logfile, "a+");
        $repair_num = 0;
        $not_required_num = 0;
        $count = 0;

        if(!empty($options)) {

            $optA = $options['a'];
            $optL = $options['l'];

        }

        $repair_album_id = intval($optA);
        $len = intval($optL);
        if(0 == $repair_album_id && 0 != strcmp($optA,$all)) {

            die("Fail: -a param is incorrect");

        }

        if($repair_album_id > 0) {

            $condition .= "and `album_id` = {$repair_album_id}";
        }

        $is_first_loop = true;
        $story_count = 0;
        $id_condition = "";

        while($is_first_loop || $story_count > 0) {

            $where = $condition.$id_condition;
            $is_first_loop = false;
            $story_obj = new Story();
            $story_list = $story_obj->get_filed_list("`id`,`title`", $where, $orderby, $limit);
            $story_count = count($story_list);

            if($story_count > 0) {

                $last_index = $story_count - 1;
                $sub_story_id = $story_list[$last_index]['id'];
                $id_condition = " and id > {$sub_story_id}";
            }


            foreach ($story_list as $story) {

                $count++;
                $view_order = $story_obj->get_view_order($story['title'], $len);
                $view_order = intval($view_order);
                if (0 != $view_order) {

                    $repair_num++;
                    $story_obj->update(array('view_order' => $view_order), "`id`={$story['id']}");
                    fwrite($fp, "getStoryViewOrder: title: {$story['title']}, id: {$story['id']}, view_order: {$view_order}\n");
                } else {

                    $not_required_num++;
                    //避免未修复的一直排在最前面,将序号更改为ID
                    $story_obj->update(array('view_order' => $story['id']), "`id`={$story['id']}");
                    fwrite($fp, "doNothing: title: {$story['title']}, id: {$story['id']}, view_order: {$view_order}\n");
                }
                $story_obj->clearStoryCache($story['id']);
            }
            $story_obj->clearAlbumStoryListCache($repair_album_id);

        }

        fwrite($fp, "Done! count:{$count}, repairNum:{$repair_num},  notRequiredNum:{$not_required_num}\n");
        fclose($fp);
    }

    protected function checkLogPath() {}
}
new cron_updateStoryViewOrder();