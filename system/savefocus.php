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
        
        $manageobj = new ManageSystem();
        $uploadobj = new Upload();
        if (empty($focusid)) {
        	// add
            $picid = time();
            $path = $uploadobj->uploadFocusImage($_FILES['focuspic'], $picid);
            if (!empty($path)) {
                $content = $path;
                $manageobj->addFocusDb($content, $linkurl);
            }
        } else {
            $data = array();
            if (!empty($_FILES['focuspic'])) {
                $focusinfo = $manageobj->getFocusInfo($focusid);
                $data['picid'] = $focusinfo['picid'];
            }
            if (!empty($linkurl)) {
                $data['linkurl'] = $linkurl;
            }
            if (!empty($ordernum)) {
                $data['ordernum'] = $ordernum;
            }
            $manageobj->updateFocusInfo($focusid, $data);
        }
        
        $this->showSuccJson();
    }
}
new savefocus();
?>