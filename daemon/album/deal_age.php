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
        $manageCollectionCronLog = new ManageCollectionCronLog();
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

        //从当当匹配数据
        $manageCollectionDdLog = new ManageCollectionDdLog();
        $albumList = $albumObj->get_list_new('1','id,title','id desc',2000);
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, 'deal_age', "从当当匹配年龄开始");
        foreach ($albumList as $val){
            $contentLog = '';
            preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $val['title'], $matches);
            $title = join('', $matches[0]);
            $ddInfo = $manageCollectionDdLog->getByTitle($title);

            if(!empty($ddInfo)){
                $contentLog .= '匹配成功->'.$val['id'].'->'.$val['title'];
                if(!empty($ddInfo['age'])){
                    $contentLog .= '->age'.$ddInfo['age'];
                }
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, 'deal_age', $contentLog);
            }
        }
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, 'deal_age', "从当当匹配年龄结束");

    }
    protected function checkLogPath() {}
}
header("content-Type: text/html; charset=Utf-8");
new deal_age();