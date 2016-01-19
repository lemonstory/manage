<?php
class ManageTagNew extends ModelBase
{
    public $STORY_DB_INSTANCE = 'share_story';
    
    public $ALBUM_TAG_RELATION_TABLE = 'album_tag_relation';
    
    
    // 更新专辑到指定一个或多个标签的（热门推荐/最新上架/同龄在听）列表
    public function updateAlbumTagRelationRecommend($albumid, $tagids, $recommendcolumn)
    {
        if (empty($albumid) || empty($tagids) || empty($recommendcolumn) || !in_array($recommendcolumn, array("ishot", "isnewonline", "issameage"))) {
            $this->setError(ErrorConf::paramError());
            return false;
        }
        if (!is_array($tagids)) {
            $tagids = array($tagids);
        }
        $tagidstr = implode(",", $tagids);
        $nowtime = time();
        
        $db = DbConnecter::connectMysql($this->STORY_DB_INSTANCE);
        $sql = "UPDATE `{$this->ALBUM_TAG_RELATION_TABLE}` SET `{$recommendcolumn}` = ?, `uptime` = ? WHERE `albumid` = ? AND `tagid` IN ($tagidstr)";
        $st = $db->prepare($sql);
        $result = $st->execute(array(1, $nowtime, $albumid));
        if (empty($result)) {
            return false;
        }
        return true;
    }
    // 更新专辑取消指定标签的推荐
    public function updateAlbumTagRelationUnRecommend($albumid, $tagids, $unrecommendcolumn)
    {
        if (empty($albumid) || empty($tagids) || empty($unrecommendcolumn) || !in_array($unrecommendcolumn, array("ishot", "isnewonline", "issameage"))) {
            $this->setError(ErrorConf::paramError());
            return false;
        }
        if (!is_array($tagids)) {
            $tagids = array($tagids);
        }
        $tagidstr = implode(",", $tagids);
        $nowtime = time();
    
        $db = DbConnecter::connectMysql($this->STORY_DB_INSTANCE);
        $sql = "UPDATE `{$this->ALBUM_TAG_RELATION_TABLE}` SET `{$unrecommendcolumn}` = ?, `uptime` = ? WHERE `albumid` = ? AND `tagid` IN ($tagidstr)";
        $st = $db->prepare($sql);
        $result = $st->execute(array(0, $nowtime, $albumid));
        if (empty($result)) {
            return false;
        }
        return true;
    }
}