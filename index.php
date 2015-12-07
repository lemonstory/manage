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
        
        // 今日新增
        $nowdaystime = date("Y-m-d 00:00:00", time());
        $nowdayetime = $nowdaystime + 86400;
        $favlist = $anobj->getAnalyticsDayList("fav", $nowdaystime, $nowdayetime);
        
        
        
        // 新增注册用户数、对比昨天增长百分比、上周对比增长百分比
        
        // 收听故事量
        
        // 用户收藏故事专辑量
        
        // 用户下载故事量
        
        // 评论总数
        
        // 专辑总数
        
        // 故事总数
        
        
        // 用户反馈总数
        
        // 待回复总数
        
        $smartyobj = $this->getSmartyObj();
        $smartyobj->assign("indexactive", "active");
        $smartyobj->assign("indexside", "active");
        $smartyobj->assign("headerdata", $this->headerCommonData($uid));
        $smartyobj->display("index.html");
    }
}
new index();
?>