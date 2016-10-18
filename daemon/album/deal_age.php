<?php
/**
 * Created by PhpStorm.
 * User: jack
 * Date: 2016/10/18
 * Time: 下午8:19
 */

//完善专辑年龄信息

//儿歌(tag)的专辑全部自动设置为0-2岁
include_once (dirname ( dirname ( __FILE__ ) ) . "/DaemonBase.php");

class deal_age extends DaemonBase {
    protected $isWhile = false;

    protected function deal() {
        $manageTagNewObj =new ManageTagNew();
        $albumObj = new Album();
        $magegeAlbumTagRelationObj = new ManageAlbumTagRelation();

        $tagList = $manageTagNewObj->getTagListByColumnSearch('id=1 or pid=1');
        foreach ($tagList as $val){
            //获取tag对应的专辑
            $albumList = $magegeAlbumTagRelationObj->getAlbumListByTagId(array('tagid'=>$val['id']),1,10000);
            foreach ($albumList as $value){
                //$albumObj->update(array('min_age'=>0,'max_age'=>2),'id='.$value['id']);
                exit();
            }
        }
    }
    protected function checkLogPath() {}
}
new deal_age();