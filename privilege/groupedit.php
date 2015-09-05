<?php
include_once '../controller.php';

class groupedit extends controller
{
    public function action()
    {
        $gid = $this->getRequest('id');
        $grouppriv = $this->getRequest('grouppriv');
        $pObj = new ManagePrivilege();
        if (!empty($grouppriv)){
            $priv = implode(',', $grouppriv);
            $ret = $pObj->editGroup($gid, $priv);
        }
        $groupinfo = current($pObj->getGroup($gid));
        $gp = explode(',', $groupinfo['privilege']);
        $groupinfo['privileges'] = $gp;
        $privileges  = $pObj->getPrivileges('');
        foreach ($privileges as $k=>$p){
            $pid = $p['id'];
            $privileges[$k]['own'] = in_array($pid, $gp);
        }
        $smartyobj = $this->getSmartyObj();
        $smartyobj->assign('groupinfo', $groupinfo);
        $smartyobj->assign('privileges', $privileges);
        $smartyobj->assign("privilege", "active");
        $smartyobj->assign("grouplistside", "active");
        $smartyobj->display("privilege/groupedit.html");
    }
}
new groupedit();
?>