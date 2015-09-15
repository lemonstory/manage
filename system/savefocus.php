<?php
include_once '../controller.php';

class savefocus extends controller
{
    public function action()
    {
        var_dump($_FILES['focuspic']);
        $focusid = $this->getRequest("focusid");
        $linkurl = $this->getRequest("linkurl");
        $ordernum = $this->getRequest("ordernum");
        if (empty($_FILES['focuspic']) || empty($linkurl) || empty($ordernum)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $manageobj = new ManageSystem();
        $uploadobj = new Upload();
        if (empty($focusid)) {
        	// add
            $picid = $manageobj->getMaxPicId();
            $path = $uploadobj->uploadFocusImage($_FILES['focuspic'], $picid);
            if (!empty($path)) {
                $manageobj->addFocusDb($picid, $linkurl);
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
            if (!empty($ordernum)) {
                $data['ordernum'] = $ordernum;
            }
            $data['status'] = 0;
            $manageobj->updateFocusInfo($focusid, $data);
        }
        
        $this->showSuccJson();
    }
}
new savefocus();
?>