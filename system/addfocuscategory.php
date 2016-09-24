<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 2016/9/24
 * Time: 下午5:26
 */

include_once '../controller.php';

class addfocuscategory extends controller
{
    public function action()
    {
        $focusid = $this->getRequest('focusid');
        $focusinfo = array();
        $manageobj = new ManageSystem();
        if (!empty($focusid)) {
            // edit
            $focusinfo = $manageobj->getFocusInfo($focusid);
            if (!empty($focusinfo)) {
                $aliossobj = new AliOss;
                $focusinfo['cover'] = $aliossobj->getFocusUrl($focusinfo['id'], $focusinfo['covertime'], 1);
            }
        }

        $smartyObj = $this->getSmartyObj();
        $smartyObj->assign("focusinfo", $focusinfo);
        $smartyObj->assign('indexactive', "active");
        $smartyObj->assign('focusside', "active");
        $smartyObj->assign("headerdata", $this->headerCommonData());
        $smartyObj->display("system/addfocuscategory.html");
    }
}
new addfocuscategory();
?>