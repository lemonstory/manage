<?php
class ManageAlbum extends ModelBase 
{
    public function getAlbumList($where = array(), $currentPage = 1, $perPage = 50) 
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
            $where = " where {$where} ";
        } else {
            $where = '';
        }
        $offset = ($currentPage - 1) * $perPage;
        
        $list = array();
        $db = DbConnecter::connectMysql('share_story');
        $sql = "SELECT * FROM `album` {$where} ORDER BY `id` DESC LIMIT {$offset}, {$perPage}";
        
        $st = $db->prepare($sql);
        $st->execute();
        $list = $st->fetchAll(PDO::FETCH_ASSOC);
        return $list;
    }
    
    public function getAlbumTotalCount($where = array())
    {
        $db = DbConnecter::connectMysql('share_story');
        if ($where) {
            $where = " where {$where} ";
        } else {
            $where = '';
        }
        $sql = "SELECT COUNT(*) FROM `album`";
        
        $st = $db->prepare($sql);
        $st->execute();
        $count = $st->fetch(PDO::FETCH_COLUMN);
        return $count;
    }
    
}

?>