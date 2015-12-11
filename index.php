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
        
        $lastdayrastlist['favsymbol'] = $lastdayrastlist['listensymbol'] = $lastdayrastlist['commentsymbol'] = $lastdayrastlist['regsymbol'] = $lastdayrastlist['downsymbol'] = "+";
        if ($lastdayrastlist['favrast'] < 0) {
            $lastdayrastlist['favsymbol'] = "-";
        }
        if ($lastdayrastlist['listenrast'] < 0) {
            $lastdayrastlist['listensymbol'] = "-";
        }
        if ($lastdayrastlist['commentrast'] < 0) {
            $lastdayrastlist['commentsymbol'] = "-";
        }
        if ($lastdayrastlist['regrast'] < 0) {
            $lastdayrastlist['regsymbol'] = "-";
        }
        if ($lastdayrastlist['downrast'] < 0) {
            $lastdayrastlist['downsymbol'] = "-";
        }
        $lastdayrastlist['favrast'] = abs($lastdayrastlist['favrast']);
        $lastdayrastlist['listenrast'] = abs($lastdayrastlist['listenrast']);
        $lastdayrastlist['commentrast'] = abs($lastdayrastlist['commentrast']);
        $lastdayrastlist['regrast'] = abs($lastdayrastlist['regrast']);
        $lastdayrastlist['downrast'] = abs($lastdayrastlist['downrast']);
        
        
        // 计算上周同比增长或下降率
        $lastweekrastlist = array();
        $lastweekrastlist['favrast'] = number_format((($nowdayfavcount / $lastweekfavcount) - 1), 2) * 100;
        $lastweekrastlist['listenrast'] = number_format((($nowdaylistencount / $lastweeklistencount) - 1), 2) * 100;
        $lastweekrastlist['commentrast'] = number_format((($nowdaycommentcount / $lastweekcommentcount) - 1), 2) * 100;
        $lastweekrastlist['regrast'] = number_format((($nowdayregcount / $lastweekregcount) - 1), 2) * 100;
        $lastweekrastlist['downrast'] = number_format((($nowdaydowncount / $lastweekdowncount) - 1), 2) * 100;
        
        $lastweekrastlist['favsymbol'] = $lastweekrastlist['listensymbol'] = $lastweekrastlist['commentsymbol'] = $lastweekrastlist['regsymbol'] = $lastweekrastlist['downsymbol'] = "+";
        if ($lastweekrastlist['favrast'] < 0) {
            $lastweekrastlist['favsymbol'] = "-";
        }
        if ($lastweekrastlist['listenrast'] < 0) {
            $lastweekrastlist['listensymbol'] = "-";
        }
        if ($lastweekrastlist['commentrast'] < 0) {
            $lastweekrastlist['commentsymbol'] = "-";
        }
        if ($lastweekrastlist['regrast'] < 0) {
            $lastweekrastlist['regsymbol'] = "-";
        }
        if ($lastweekrastlist['downrast'] < 0) {
            $lastweekrastlist['downsymbol'] = "-";
        }
        $lastweekrastlist['favrast'] = abs($lastweekrastlist['favrast']);
        $lastweekrastlist['listenrast'] = abs($lastweekrastlist['listenrast']);
        $lastweekrastlist['commentrast'] = abs($lastweekrastlist['commentrast']);
        $lastweekrastlist['regrast'] = abs($lastweekrastlist['regrast']);
        $lastweekrastlist['downrast'] = abs($lastweekrastlist['downrast']);
        
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