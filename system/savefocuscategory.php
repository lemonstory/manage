<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 2016/9/25
 * Time: 上午10:02
 */
include_once '../controller.php';

class savefocuscategory extends controller
{
    public function action()
    {
        $name = $this->getRequest("name");
        $enName = $this->getRequest("en_name");
        $focusCategoryId = $this->getRequest("focus_category_id",'');

        if (empty($name) || empty($enName)) {
            $this->showErrorJson(ErrorConf::paramError());
        }
        $manageFocusCategoryObj = new ManageFocusCategory();

        if (empty($focusCategoryId)) {
            // add
            $data=array('name'=>$name,'en_name'=>$enName,'status'=>0,'create_time'=>date('Y-m-d H:i:s'));
            $focusCategoryId = $manageFocusCategoryObj->add($data);
        } else {
            // edit

        }

        $this->redirect("/system/focuslist.php");
    }
}
new savefocuscategory();
?>