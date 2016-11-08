<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 2016/11/7
 * Time: 下午8:05
 */

//将从当当采集到的作者信息匹配过来
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");

class deal_author extends DaemonBase {
    protected $isWhile = false;

    protected function deal() {
        $manageCollectionCronLog = new ManageCollectionCronLog();


        //从当当匹配数据
        $manageCollectionDdLog = new ManageCollectionDdLog();
        $creator = new Creator();
        $whereArr = array(
            "is_author" => 1,
        );
        $authorList = $creator->getCreatorList($whereArr, 1, 2000);
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, 'deal_author', "从当当匹配作者开始");
        foreach ($authorList as $val){
            $contentLog = '';
            $authorInfo = $manageCollectionDdLog->getByAuthor($val['nickname']);
            if(!empty($authorInfo)){
                $contentLog .='成功匹配作者'.$val['nickname'].'->dd_id:'.$authorInfo['dd_id'];
                if(!empty($authorInfo['about_the_author'])){
                    $contentLog .='->'.$authorInfo['about_the_author'];
                    //修改作者简介
                    $data = array('intro'=>$authorInfo['about_the_author']);
                    $where = 'uid='.$val['uid'];
                    //$creator->update($data,$where);
                }
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_SUCESS, 'deal_author', $contentLog);
            }
        }
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, 'deal_author', "从当当匹配作者结束");
    }

    protected function checkLogPath() {}
}

header("content-Type: text/html; charset=Utf-8");
new deal_author();