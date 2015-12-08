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
        $nowdaystime = date("Ymd", time());
        $nowdayetime = date("Ymd", strtotime($nowdaystime) + 86400);
        $lastdaystime = date("Ymd", strtotime($nowdaystime) - 86400);
        $lastweekstime = date("Ymd", strtotime($nowdaystime) - 7 * 86400);
        
        // 获取7天内的统计数据
        $sevendayfavlist = $anobj->getAnalyticsDayList("fav", $lastweekstime, $nowdayetime);
        $sevendaylistenlist = $anobj->getAnalyticsDayList("listen", $lastweekstime, $nowdayetime);
        $sevendaycommentlist = $anobj->getAnalyticsDayList("comment", $lastweekstime, $nowdayetime);
        $sevendayreglist = $anobj->getAnalyticsDayList("reg", $lastweekstime, $nowdayetime);
        $sevendaydownlist = $anobj->getAnalyticsDayList("down", $lastweekstime, $nowdayetime);
        
//         $nowdayfavcount = $nowdayfavlist['totalnum'];
//         $nowdaylistencount = $nowdaylistenlist['totalnum'];
//         $nowdaycommentcount = $nowdaycommentlist['totalnum'];
//         $nowdayregcount = $nowdayreglist['personnum'];
        
        $nowdayfavcount = $lastdayfavcount = $lastweekfavcount = 0;
        if (!empty($sevendayfavlist)) {
            foreach ($sevendayfavlist as $info) {
                $timeline = $info['timeline'];
                if ($timeline == $nowdaystime) {
                    // 今日日统计数据
                    $nowdayfavcount = $info['totalnum'] + 0;
                } elseif ($timeline == $lastdaystime) {
                    // 昨日日统计数据
                    $lastdayfavcount = $info['totalnum'] + 0;
                } elseif ($timeline == $lastweekstime) {
                    // 上周同日统计数据
                    $lastweekfavcount = $info['totalnum'] + 0;
                }
            }
        }
        
        $nowdaylistencount = $lastdaylistencount = $lastweeklistencount = 0;
        if (!empty($sevendaylistenlist)) {
            foreach ($sevendaylistenlist as $info) {
                $timeline = $info['timeline'];
                if ($timeline == $nowdaystime) {
                    $nowdaylistencount = $info['totalnum'] + 0;
                } elseif ($timeline == $lastdaystime) {
                    $lastdaylistencount = $info['totalnum'] + 0;
                } elseif ($timeline == $lastweekstime) {
                    $lastweeklistencount = $info['totalnum'] + 0;
                }
            }
        }
        
        $nowdaycommentcount = $lastdaycommentcount = $lastweekcommentcount = 0;
        if (!empty($sevendaycommentlist)) {
            foreach ($sevendaycommentlist as $info) {
                $timeline = $info['timeline'];
                if ($timeline == $nowdaystime) {
                    $nowdaycommentcount = $info['totalnum'] + 0;
                } elseif ($timeline == $lastdaystime) {
                    $lastdaycommentcount = $info['totalnum'] + 0;
                } elseif ($timeline == $lastweekstime) {
                    $lastweekcommentcount = $info['totalnum'] + 0;
                }
            }
        }
        
        $nowdayregcount = $lastdayregcount = $lastweekregcount = 0;
        if (!empty($sevendayreglist)) {
            foreach ($sevendayreglist as $info) {
                $timeline = $info['timeline'];
                if ($timeline == $nowdaystime) {
                    $nowdayregcount = $info['personnum'] + 0;
                } elseif ($timeline == $lastdaystime) {
                    $lastdayregcount = $info['personnum'] + 0;
                } elseif ($timeline == $lastweekstime) {
                    $lastweekregcount = $info['personnum'] + 0;
                }
            }
        }
        
        $nowdaydowncount = $lastdaydowncount = $lastweekdowncount = 0;
        if (!empty($sevendaydownlist)) {
            foreach ($sevendaydownlist as $info) {
                $timeline = $info['timeline'];
                if ($timeline == $nowdaystime) {
                    $nowdaydowncount = $info['totalnum'] + 0;
                } elseif ($timeline == $lastdaystime) {
                    $lastdaydowncount = $info['totalnum'] + 0;
                } elseif ($timeline == $lastweekstime) {
                    $lastweekdowncount = $info['totalnum'] + 0;
                }
            }
        }
        
        // 计算昨日对比增长或下降率
        $lastdayrastlist = array();
        $lastdayrastlist['favrast'] = number_format((($nowdayfavcount / $lastdayfavcount) - 1), 2) * 100;
        $lastdayrastlist['listenrast'] = number_format((($nowdaylistencount / $lastdaylistencount) - 1), 2) * 100;
        $lastdayrastlist['commentrast'] = number_format((($nowdaycommentcount / $lastdaycommentcount) - 1), 2) * 100;
        $lastdayrastlist['regrast'] = number_format((($nowdayregcount / $lastdayregcount) - 1), 2) * 100;
        $lastdayrastlist['downrast'] = number_format((($nowdaydowncount / $lastdaydowncount) - 1), 2) * 100;
        
        // 计算上周同比增长或下降率
        $lastweekrastlist = array();
        $lastweekrastlist['favrast'] = number_format((($nowdayfavcount / $lastweekfavcount) - 1), 2) * 100;
        $lastweekrastlist['listenrast'] = number_format((($nowdaylistencount / $lastweeklistencount) - 1), 2) * 100;
        $lastweekrastlist['commentrast'] = number_format((($nowdaycommentcount / $lastweekcommentcount) - 1), 2) * 100;
        $lastweekrastlist['regrast'] = number_format((($nowdayregcount / $lastweekregcount) - 1), 2) * 100;
        $lastweekrastlist['downrast'] = number_format((($nowdaydowncount / $lastweekdowncount) - 1), 2) * 100;
        
        
        // 专辑总数
        
        // 故事总数
        
        // 用户反馈总数
        
        // 待回复总数
        
        $smartyobj = $this->getSmartyObj();
        $smartyobj->assign("indexactive", "active");
        $smartyobj->assign("indexside", "active");
        $smartyobj->assign("lastdayrastlist", $lastdayrastlist);
        $smartyobj->assign("lastweekrastlist", $lastweekrastlist);
        $smartyobj->assign("headerdata", $this->headerCommonData($uid));
        $smartyobj->display("index.html");
    }
}
new index();
?>