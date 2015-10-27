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
        
        // 用户总数
        
        // 新增用户数、按天图表
        
        // 收听故事量、按天图表
        
        // 用户收藏故事专辑量、按天图表
        
        // 用户下载故事量、按天图表
        
        
        // 专辑总数
        // 故事总数
        
        // 评论总数
        
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