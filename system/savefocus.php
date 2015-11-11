<?php
include_once '../controller.php';

class savefocus extends controller
{
    public function action()
    {
        $focusid = $this->getRequest("focusid");
        $linktype = $this->getRequest("linktype");
        $linkurl = $this->getRequest("linkurl");
        $ordernum = $this->getRequest("ordernum");
        if (empty($_FILES['focuspic']) || empty($linktype) || empty($linkurl)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $manageobj = new ManageSystem();
        $uploadobj = new Upload();
        if (empty($focusid)) {
        	// add
            $picid = $manageobj->getMaxPicId();
            $path = $uploadobj->uploadFocusImage($_FILES['focuspic'], $picid);
            if (!empty($path)) {
                $manageobj->addFocusDb($picid, $linktype, $linkurl, $ordernum);
            }
        } else {
            // edit
            $data = array();
            if (!empty($_FILES['focuspic'])) {
                $picid = $manageobj->getMaxPicId();
                $path = $uploadobj->uploadFocusImage($_FILES['focuspic'], $picid);
                if (!empty($path)) {
                    // 更新picid
                    $data['picid'] = $picid;
                }
            }
            if (!empty($linkurl)) {
                $data['linkurl'] = $linkurl;
            }
            if (!empty($linktype)) {
                $data['linktype'] = $linktype;
            }
            if (!empty($ordernum)) {
                $data['ordernum'] = $ordernum;
            }
            
            $configvarobj = new ConfigVar();
            $data['status'] = $configvarobj->RECOMMEND_STATUS_OFFLINE;
            $manageobj->updateFocusInfo($focusid, $data);
        }
        
        //$this->showSuccJson();
        $this->redirect("/system/focuslist.php");
    }
}
new savefocus();
?>