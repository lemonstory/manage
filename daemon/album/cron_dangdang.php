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

    protected function deal() {
        $httpObj = new Http();

        $manageCollectionCronLog = new ManageCollectionCronLog();
        $manageCollectionDdLog = new ManageCollectionDdLog();
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, 'dd_books', "采集当当开始");
        for($i=4206596;$i<4500000;$i++){
        //for($i=21121736;$i<21500000;$i++){
            $url = 'http://product.dangdang.com/'.$i.'.html';
            $content = $httpObj->get($url);
            $tmp = $httpObj->sub_data($content,'<li class="clearfix fenlei"','</li>');
            $contentLog = "";
            $data = array();
            if(!empty($tmp)){
                $contentLog .= "url->".$url.",";
                $data['dd_id'] = $i;
                $data['url'] = $url;
                $titleAll = $httpObj->sub_data($content,'<title>','</title>');
                $titleAll = mb_convert_encoding($titleAll, 'utf-8', 'GBK,UTF-8,ASCII');


                $titleArr = explode('》',$titleAll);
                $title = str_replace("《","",$titleArr[0]);
                $contentLog .= "title->".$title.",";
                $data['title'] = $title;

                //采集作者信息
                $data['author'] = '';
                $authorArr = $httpObj->sub_data($titleAll,'(',')');
                if(!empty($authorArr)){
                    $authorArr = explode(' ',$authorArr);
                    $data['author'] = $authorArr[0];
                }

                //年龄处理
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

                if(!empty($data['age'])){
                    //获取编辑推荐作者简介等
                    $tmp1 = $httpObj->sub_data($content,'var prodSpuInfo = ',';');
                    $tmp1Arr = json_decode($tmp1,true);
                    $contentUrl = '';
                    $contentUrl .= 'http://product.dangdang.com/?r=callback%2Fdetail';
                    $contentUrl .= '&productId='.$i;
                    $contentUrl .= '&templateType='.$tmp1Arr['template'];
                    $contentUrl .= '&describeMap='.$tmp1Arr['describeMap'];
                    $contentUrl .= '&shopId='.$tmp1Arr['shopId'];
                    $contentUrl .= '&categoryPath='.$tmp1Arr['categoryPath'];

                    $dataJson = $httpObj->get($contentUrl);
                    $dataArr = json_decode($dataJson,true);
                    $tmpEdit = $httpObj->sub_data($dataArr['data']['html'],'编辑推荐','内容推荐');
                    $tmpEdit = trim(strip_tags($tmpEdit));
                    $data['about_the_author'] = $tmpEdit;
                    $tmpAuthor = $httpObj->sub_data($dataArr['data']['html'],'作者简介','目　　录');
                    $tmpAuthor = trim(strip_tags($tmpAuthor));
                    $data['edit_recommend'] = $tmpAuthor;
                }else{
                    $data['about_the_author'] = '';
                    $data['edit_recommend'] = '';
                }

                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, 'dd_books', $contentLog);
                if(strpos($tmp,'图书')!==false) {
                    $manageCollectionDdLog->insert($data);
                }
            }else{
                $contentLog .= $i."为空";
                $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_START, 'dd_books', $contentLog);
            }
            //sleep(1);
        }
        $manageCollectionCronLog->writeLog(ManageCollectionCronLog::ACTION_SPIDER_END, 'dd_books', "采集当当结束");




    }
    protected function checkLogPath() {}
}

new cron_dangdang();