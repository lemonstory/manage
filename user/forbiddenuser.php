<?php
/*
 * 封号
 */
include_once '../controller.php';

class forbiddenuseraction extends controller
{
    public function action()
    { 
        $userObj = new User();
        $ossObj = new AliOss();
        $forbiddenObj = new ForbiddenUser();
        $forbiddenuid = $this->getRequest('forbiddenuid');
        $reason = $this->getRequest('reason');
        $admininput = $this->getRequest("admininput");
        if (!empty($reason)){
            $result = $forbiddenObj->addforbidden($forbiddenuid, $reason, $admininput);
            $this->showSuccJson();
        }
        $userinfo = current($userObj->getUserInfo($forbiddenuid));
        $userinfo['avatar'] = $ossObj->getAvatarUrl($forbiddenuid, @$userinfo['avatartime'], 80);
        $frozenreasons = $forbiddenObj->FORBIDDENUSER_REASONS;
        
        $refer = "";
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $refer = $_SERVER['HTTP_REFERER'];
        }
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('userinfo', $userinfo);
        $smartyObj->assign('reasons', $frozenreasons);
        $smartyObj->assign('refer', $refer);
        $smartyObj->assign('useractive', "active");
        $smartyObj->assign('getuserlistside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("user/forbiddenuser.html");
    }
}
new forbiddenuseraction();