<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 2016/10/18
 * Time: 下午8:19
 */

//完善专辑年龄信息

//儿歌(tag)的专辑全部自动设置为0-2岁
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");

class deal_age extends DaemonBase {
    protected $isWhile = false;
    public static $is_ajax    = false;
    public static $cookie     = '';
    public static $user_agent = 'User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.124 Safari/537.36';
    public static $referer    = 'http://www.baidu.com/';

    protected function deal() {
        $manageTagNewObj =new ManageTagNew();
        $albumObj = new Album();
        $magegeAlbumTagRelationObj = new ManageAlbumTagRelation();

        $tagList = array();//$manageTagNewObj->getTagListByColumnSearch('id=1 or pid=1');
        foreach ($tagList as $val){
            //获取tag对应的专辑
            $albumList = $magegeAlbumTagRelationObj->getAlbumListByTagId(array('tagid'=>$val['id']),1,10000);
            foreach ($albumList as $value){
                //$albumObj->update(array('min_age'=>0,'max_age'=>2),'id='.$value['id']);
                //exit();
            }
        }

        $httpObj = new Http();

        $manageCollectionCronLog = new ManageCollectionCronLog();
        $manageCollectionDdLog = new ManageCollectionDdLog();
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, 'deal_age_dangdang', "test_dangdang开始");
        for($i=1;$i<2;$i++){
            $url = 'http://product.dangdang.com/23690777.html';
            $content = $httpObj->get($url);
            $tmp = $httpObj->sub_data($content,'<li class="clearfix fenlei"','</li>');
            $contentLog = "";
            $data = array();
            if(!empty($tmp)){
                $contentLog .= "url->".$url.",";
                $data['dd_id'] = 23690777;
                $data['url'] = $url;
                $title = $httpObj->sub_data($content,'<title>','</title>');
                $title = mb_convert_encoding($title, 'utf-8', 'GBK,UTF-8,ASCII');
                $titleArr = explode('》',$title);
                $title = str_replace("《","",$titleArr[0]);
                $contentLog .= "title->".$title.",";
                $data['title'] = $title;
                $tmp = mb_convert_encoding($tmp, 'utf-8', 'GBK,UTF-8,ASCII');
                $ageArr = array('0-2','3-6','7-10','11-14');
                foreach ($ageArr as $value){
                    if(strpos($tmp,$value)){
                        $contentLog .= 'age->'.$value;
                        $data['age'] = $value;
                        break;
                    }
                }
                var_dump($contentLog);
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, 'deal_age_dangdang', $contentLog);
                $manageCollectionDdLog->insert($data);
            }
            sleep(10);
        }
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, 'deal_age_dangdang', "test_dangdang结束");




    }
    protected function checkLogPath() {}
}
header("content-Type: text/html; charset=Utf-8");
new deal_age();