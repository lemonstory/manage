<?php
class ManageUserFeedback extends ModelBase 
{
    public function getUserFeedbackList($where = array(), $currentPage = 1, $perPage = 50) 
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
        $db = DbConnecter::connectMysql('share_main');
        $sql = "SELECT * FROM `user_feed_back` {$where} ORDER BY `id` DESC LIMIT {$offset}, {$perPage}";
        
        $st = $db->prepare($sql);
        $st->execute();
        $list = $st->fetchAll(PDO::FETCH_ASSOC);
        return $list;
    }
    
    public function getUserFeedbackTotalCount($where = array())
    {
        $db = DbConnecter::connectMysql('share_main');
        if ($where) {
            $where = " where {$where} ";
        } else {
            $where = '';
        }
        $sql = "SELECT COUNT(*) FROM `user_feed_back` {$where}";
        
        $st = $db->prepare($sql);
        $st->execute();
        $count = $st->fetch(PDO::FETCH_COLUMN);
        return $count;
    }

    public function getByFeedbackId($id)
    {
        if (!is_array($id)) {
            $id = array($id);
        }
        $ids = implode(",", $id);
        if (!$id) {
            return array();
        }
        $db = DbConnecter::connectMysql('share_main');
        $sql = "SELECT * FROM `user_feed_back` where `replyid` in ({$ids})";
        
        $st = $db->prepare($sql);
        $st->execute();
        $list = $st->fetchAll(PDO::FETCH_ASSOC);
        $newlist = array();
        foreach($list as $k => $v) {
            $newlist[$v['replyid']] = $v;
        }
        unset($list);
        var_dump($newlist);
        return $newlist;
    }
    
}

?>