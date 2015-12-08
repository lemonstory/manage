<?php
include_once './controller.php';

class index extends controller
{
    public function filters()
    {
        return array(
            'authLogin' => array(
                'requireLogin' => false,
             ),
            'privilege' => array(
                'checkPrivilege' => false,
            ),
        );
    }
    
    public function action()
    {
        $uid = $this->getUid();
        if (empty($uid)) {
            $this->redirect('/user/login.php');
        }
        $anobj = new Analytics();
        
        // 今日日统计数据
        $nowdaystime = date("Y-m-d 00:00:00", time());
        $nowdayetime = $nowdaystime + 86400;
        $nowdayfavlist = $anobj->getAnalyticsDayList("fav", $nowdaystime, $nowdayetime);
        $nowdaylistenlist = $anobj->getAnalyticsDayList("listen", $nowdaystime, $nowdayetime);
        $nowdaycommentlist = $anobj->getAnalyticsDayList("comment", $nowdaystime, $nowdayetime);
        $nowdayreglist = $anobj->getAnalyticsDayList("reg", $nowdaystime, $nowdayetime);
        $nowdaydownlist = $anobj->getAnalyticsDayList("down", $nowdaystime, $nowdayetime);
        
        // 昨日日统计数据
        $lastdaystime = $nowdaystime - 86400;
        $lastdayetime = $nowdaystime;
        $lastdayfavlist = $anobj->getAnalyticsDayList("fav", $lastdaystime, $lastdayetime);
        $lastdaylistenlist = $anobj->getAnalyticsDayList("listen", $lastdaystime, $lastdayetime);
        $lastdaycommentlist = $anobj->getAnalyticsDayList("comment", $lastdaystime, $lastdayetime);
        $lastdayreglist = $anobj->getAnalyticsDayList("reg", $lastdaystime, $lastdayetime);
        $lastdaydownlist = $anobj->getAnalyticsDayList("down", $lastdaystime, $lastdayetime);
        
        
        $nowdayfavcount = $nowdayfavlist['totalnum'];
        $nowdaylistencount = $nowdayfavlist['totalnum'];
        $nowdaycommentcount = $nowdayfavlist['totalnum'];
        $nowdayregcount = $nowdayfavlist['personnum'];
        $nowdaydowncount = $nowdayfavlist['totalnum'];
        
        $lastdayfavcount = $lastdayfavlist['totalnum'];
        $lastdaylistencount = $lastdayfavlist['totalnum'];
        $lastdaycommentcount = $lastdayfavlist['totalnum'];
        $lastdayregcount = $lastdayfavlist['personnum'];
        $lastdaydowncount = $lastdayfavlist['totalnum'];
        
        
        // 计算增长或下降率
        $rastlist = array();
        $rastlist['tolastdayfavrast'] = number_format((($nowdayfavcount / $lastdayfavcount) - 1), 2) * 100;
        $rastlist['tolastdaylistenrast'] = number_format((($nowdaylistencount / $lastdaylistencount) - 1), 2) * 100;
        $rastlist['tolastdaycommentrast'] = number_format((($nowdaycommentcount / $lastdaycommentcount) - 1), 2) * 100;
        $rastlist['tolastdayregrast'] = number_format((($nowdayregcount / $lastdayregcount) - 1), 2) * 100;
        $rastlist['tolastdaydownrast'] = number_format((($nowdaydowncount / $lastdaydowncount) - 1), 2) * 100;
        
        
        // 专辑总数
        
        // 故事总数
        
        // 用户反馈总数
        
        // 待回复总数
        
        $smartyobj = $this->getSmartyObj();
        $smartyobj->assign("indexactive", "active");
        $smartyobj->assign("indexside", "active");
        $smartyobj->assign("rastlist", $rastlist);
        $smartyobj->assign("headerdata", $this->headerCommonData($uid));
        $smartyobj->display("index.html");
    }
}
new index();
?>