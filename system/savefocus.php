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
        $category_en_name = $this->getRequest("select_category_en_name");
        if (empty($_FILES['focuspic']) || empty($linktype) || empty($linkurl) || empty($category_en_name)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        
        $manageobj = new ManageSystem();
        $uploadobj = new Upload();
        if (empty($focusid)) {
        	// add
            $focusid = $manageobj->addFocusDb($linktype, $linkurl, $ordernum,$category_en_name);

            if (!empty($focusid)) {
                $uploadobj->uploadFocusImageByPost($_FILES['focuspic'], $focusid);
            }
        } else {
            // edit
            $data = array();
            if (!empty($_FILES['focuspic']) && !empty($_FILES['focuspic']['tmp_name']) && $_FILES['focuspic']['error'] === 0) {
                $path = $uploadobj->uploadFocusImageByPost($_FILES['focuspic'], $focusid);
                if (!empty($path)) {
                    // 更新picid
                    $data['covertime'] = time();
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
            if (!empty($category_en_name)) {
                $data['category'] = $category_en_name;
            }
            $configvarobj = new ConfigVar();
            $data['status'] = $configvarobj->RECOMMEND_STATUS_OFFLINE;
            $manageobj->updateFocusInfo($focusid, $data);
        }
        
        $this->redirect("/system/focuslist.php");
    }
}
new savefocus();
?>