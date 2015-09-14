<?php
class ManageComment extends ModelBase 
{
    public function getCommentList($where = '', $currentPage = 1, $perPage = 50) 
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
        $db = DbConnecter::connectMysql('share_comment');
        $sql = "SELECT * FROM `album_comment` {$where} ORDER BY `id` DESC LIMIT {$offset}, {$perPage}";
        
        $st = $db->prepare($sql);
        $st->execute();
        $list = $st->fetchAll(PDO::FETCH_ASSOC);
        return $list;
    }
    
    public function getCommentTotalCount($where)
    {
        $db = DbConnecter::connectMysql('share_comment');
        if ($where) {
            $where = " where {$where} ";
        } else {
            $where = '';
        }
        $sql = "SELECT COUNT(*) FROM `album_comment` {$where}";
        
        $st = $db->prepare($sql);
        $st->execute();
        $count = $st->fetch(PDO::FETCH_COLUMN);
        return $count;
    }
    
}

?>