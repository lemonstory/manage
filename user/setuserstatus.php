<?php
include_once '../controller.php';

class setuserstatus extends controller
{
    public function action()
    {
        $loginuid = $this->getUid();
        $uid = $this->getRequest('uid', 0) + 0;
        $status = $this->getRequest('status', 0);
        $currentPage = $this->getRequest('p') + 0;
        $perPage = $this->getRequest('perPage', 50) + 0;
        $searchCondition = $this->getRequest('searchCondition', '');
        $searchContent = $this->getRequest('searchContent', '');
        
        $url = "/user/getuserlist.php?p={$currentPage}&perPage={$perPage}&searchCondition={$searchCondition}&searchContent={$searchContent}";
        $configvarobj = new ConfigVar();
        if (empty($uid) || !in_array($status, $configvarobj->OPTION_STATUS_LIST)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $userObj = new User();
        $userList = $userObj->getUserInfo($uid);
        if (empty($userList)) {
            $this->showErrorJson(ErrorConf::userNoExist());
        }
        $userInfo = current($userList);
        $data = array('uid' => $userInfo['uid']);
        
        if ($userInfo['status'] == $status) {
            $this->redirect($url);
        }
        
        $setData = array('status' => $status);
        $result = $userObj->setUserinfo($uid, $setData);
        if(empty($result)) {
            $this->showErrorJson($userObj->getError());
        }
        /*if ($userInfo['status'] == -1 && $status == 1) {
            // 解除冻结
            $actionrecordobj = new ActionRecord();
            $actionrecordobj->unFrozenUserRecord($loginuid, $uid);
        } elseif ($userInfo['status'] == -2 && $status == 1) {
            // 解除封号
            $actionrecordobj = new ActionRecord();
            $actionrecordobj->unForbiddenUserRecord($loginuid, $uid);
        }*/
        
        $this->showSuccJson();
    }
}
new setuserstatus();