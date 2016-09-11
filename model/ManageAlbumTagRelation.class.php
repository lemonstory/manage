<?php
class ManageAlbumTagRelation extends ModelBase
{
    public $CACHE_INSTANCE = 'cache';

    public function getAlbumListByTagId($where = array(), $currentPage = 1, $perPage = 50)
    {
        if (empty($currentPage)) {
            $currentPage = 1;
        }
        if ($currentPage <= 0) {
            $currentPage = 1;
        }
        if (empty($perPage)) {
            $perPage = 50;
        }
        if ($perPage <= 0) {
            $perPage = 50;
        }
        if ($where) {
            $whereStr = ' WHERE 1 ';
            foreach ($where as $key=>$val){
                $whereStr .= " and `{$key}`=:{$key}";
            }
        } else {
            $whereStr = '';
        }
        $offset = ($currentPage - 1) * $perPage;
        
        $list = array();
        $db = DbConnecter::connectMysql('share_story');
        $sql = "SELECT * FROM `album_tag_relation` {$whereStr} ORDER BY `id` DESC LIMIT {$offset}, {$perPage}";

        $st = $db->prepare($sql);
        $st->execute($where);
        $list = $st->fetchAll(PDO::FETCH_ASSOC);
        return $list;
    }

    public function getAlbumTagRelationTotalCount($where = array())
    {
        $db = DbConnecter::connectMysql('share_story');
        if ($where) {
            $whereStr = ' WHERE 1 ';
            foreach ($where as $key=>$val){
                $whereStr .= " and `{$key}`=:{$key}";
            }
        } else {
            $whereStr = '';
        }
        $sql = "SELECT COUNT(*) FROM `album_tag_relation` $whereStr";

        $st = $db->prepare($sql);
        $st->execute($where);
        $count = $st->fetch(PDO::FETCH_COLUMN);
        return $count;
    }
}