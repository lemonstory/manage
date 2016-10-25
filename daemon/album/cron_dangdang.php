<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 2016/10/22
 * Time: 下午5:08
 */
//从当当采集数据
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");

class cron_dangdang extends DaemonBase {
    protected $isWhile = false;
    public static $is_ajax    = false;

    protected function deal() {
        $httpObj = new Http();

        $manageCollectionCronLog = new ManageCollectionCronLog();
        $manageCollectionDdLog = new ManageCollectionDdLog();
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, 'deal_age_dangdang', "test_dangdang开始");
        for($i=591757;$i<800000;$i++){
            $url = 'http://product.dangdang.com/'.$i.'.html';
            $content = $httpObj->get($url);
            $tmp = $httpObj->sub_data($content,'<li class="clearfix fenlei"','</li>');
            $contentLog = "";
            $data = array();
            if(!empty($tmp)){
                $contentLog .= "url->".$url.",";
                $data['dd_id'] = $i;
                $data['url'] = $url;
                $title = $httpObj->sub_data($content,'<title>','</title>');
                $title = mb_convert_encoding($title, 'utf-8', 'GBK,UTF-8,ASCII');
                $titleArr = explode('》',$title);
                $title = str_replace("《","",$titleArr[0]);
                $contentLog .= "title->".$title.",";
                $data['title'] = $title;
                $tmp = mb_convert_encoding($tmp, 'utf-8', 'GBK,UTF-8,ASCII');
                $ageArr = array('0-2','3-6','7-10','11-14');
                $data['age'] = '';
                foreach ($ageArr as $value){
                    if(strpos($tmp,$value)){
                        $contentLog .= 'age->'.$value;
                        $data['age'] = $value;
                        break;
                    }
                }
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, 'deal_age_dangdang', $contentLog);
                $manageCollectionDdLog->insert($data);
            }else{
                $contentLog .= $i."为空";
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, 'deal_age_dangdang', $contentLog);
            }
            //sleep(1);
        }
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, 'deal_age_dangdang', "test_dangdang结束");




    }
    protected function checkLogPath() {}
}

new cron_dangdang();