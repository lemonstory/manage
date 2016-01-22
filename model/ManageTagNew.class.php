<?php
class ManageTagNew extends ModelBase
{
    public $ALBUM_TAG_RELATION_TABLE = 'album_tag_relation';
    
    public $RECOMMEND_COLUMN = array("isrecommend", "isnewonline", "issameage");
    
    
    /**
     * 更新专辑到指定一个或多个标签的（热门推荐/最新上架/同龄在听）列表
     * @param I $albumid            专辑ID
     * @param A $tagids             需要添加/更新推荐字段的标签id数组
     * @param S $recommendcolumn    需要更新的推荐字段：isrecommend-热门推荐，isnewonline-最新上架，issameage-同龄在听
     * @return boolean
     */
    public function updateAlbumTagRelationRecommend($albumid, $tagids, $recommendcolumn)
    {
        if (empty($albumid) || empty($tagids) || empty($recommendcolumn) || !in_array($recommendcolumn, $this->RECOMMEND_COLUMN)) {
            $this->setError(ErrorConf::paramError());
            return false;
        }
        if (!is_array($tagids)) {
            $tagids = array($tagids);
        }
        $nowtime = time();
        
        $tagnewobj = new TagNew();
        $relationlist = current($tagnewobj->getAlbumTagRelationListByAlbumIds($albumid));
        $inserttagids = array();
        $updatetagids = array();
        if (empty($relationlist)) {
            // 全部为需要新增album_tag_relation记录的tagid
            $inserttagids = $tagids;
        } else {
            $relationtagids = array_keys($relationlist);
            // 需要新增album_tag_relation记录的tagid
            $inserttagids = array_diff($tagids, $relationtagids);
            // 需要更新isrecommend/issamgeage/isnewonline=1的tagid
            $updatetagids = array_intersect($tagids, $relationtagids);
        }
        
        if (!empty($inserttagids)) {
            foreach ($inserttagids as $inserttagid) {
                // 新增关联记录，并标识为推荐
                $this->addAlbumTagRelationDbToRecommend($albumid, $inserttagid, $recommendcolumn);
            }
        }
        if (!empty($updatetagids)) {
            // 更新专辑相应的tagid的推荐状态
            $db = DbConnecter::connectMysql($this->STORY_DB_INSTANCE);
            $updatetagidstr = implode(",", $updatetagids);
            $sql = "UPDATE `{$this->ALBUM_TAG_RELATION_TABLE}` SET `{$recommendcolumn}` = ?, `uptime` = ? WHERE `albumid` = ? AND `tagid` IN ($updatetagidstr)";
            $st = $db->prepare($sql);
            $result = $st->execute(array(1, $nowtime, $albumid));
            if (empty($result)) {
                return false;
            }
        }
        return true;
    }
    
    
    // 更新专辑取消指定标签的推荐
    public function updateAlbumTagRelationUnRecommend($albumid, $unrecommendcolumn)
    {
        if (empty($albumid) || empty($unrecommendcolumn) || !in_array($unrecommendcolumn, $this->RECOMMEND_COLUMN)) {
            $this->setError(ErrorConf::paramError());
            return false;
        }
        //$nowtime = time();
        
        $db = DbConnecter::connectMysql($this->STORY_DB_INSTANCE);
        $sql = "UPDATE `{$this->ALBUM_TAG_RELATION_TABLE}` SET `{$unrecommendcolumn}` = ? WHERE `albumid` = ?";
        $st = $db->prepare($sql);
        $result = $st->execute(array(0, $albumid));
        if (empty($result)) {
            return false;
        }
        return true;
    }
    
    
    // 添加专辑标签关联记录，并标记为推荐/同龄在听/最新上架
    private function addAlbumTagRelationDbToRecommend($albumid, $tagid, $recommendcolumn)
    {
        if (empty($albumid) || empty($tagid)) {
            $this->setError(ErrorConf::paramError());
            return false;
        }
        $nowtime = time();
        $addtime = date("Y-m-d H:i:s", $nowtime);
        $db = DbConnecter::connectMysql($this->STORY_DB_INSTANCE);
        $sql = "INSERT INTO `{$this->ALBUM_TAG_RELATION_TABLE}` (`tagid`, `albumid`, `{$recommendcolumn}`, `uptime`, `addtime`) 
            VALUES (?, ?, ?, ?, ?)";
        $st = $db->prepare($sql);
        $res = $st->execute(array($tagid, $albumid, 1, $nowtime, $addtime));
        if (empty($res)) {
            return false;
        }
        return true;
    }
}