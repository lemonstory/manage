<?php
/*
 * 冻结用户
 */
include_once '../controller.php';

class frozenuseraction extends controller
{ 
    public function action()
    {
        $userObj = new User();
        $ossObj = new AliOss();
        $frozenObj = new FrozenUser();
        $frozenuid = $this->getRequest('frozenuid');
        $duration = $this->getRequest('duration');
        $reason = $this->getRequest('reason');
        $admininput = $this->getRequest('admininput');
        if (!empty($duration) && !empty($reason)){
            $result = $frozenObj->addFrozen($frozenuid, $duration, $reason, $admininput);
            $this->showSuccJson();
        }
        $userinfo = current($userObj->getUserInfo($frozenuid));
        $userinfo['avatar'] = $ossObj->getAvatarUrl($frozenuid, @$userinfo['avatartime'], 100);
        $frozenreasons = $frozenObj->FROZENUSER_REASONS;
        
        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign('userinfo', $userinfo);
        $smartyObj->assign('reasons', $frozenreasons);
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("user/frozenuser.html");
    }
}
new frozenuseraction();