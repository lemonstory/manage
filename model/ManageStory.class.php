<?php
class ManageStory extends ModelBase 
{
    public function getStoryList($where = array(), $currentPage = 1, $perPage = 50) 
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
        $sql = "SELECT * FROM `story` {$where} ORDER BY `view_order` ASC,`id` DESC LIMIT {$offset}, {$perPage}";
        $st = $db->prepare($sql);
        $st->execute();
        $list = $st->fetchAll(PDO::FETCH_ASSOC);
        return $list;
    }

    public function getStoryInfo($storyId = 0, $filed = '')
    {
        if (!$storyId) {
            return array();
        }
        
        $where = "`id`={$storyId}";
        $sql = "select * from story  where {$where} limit 1";

        $db = DbConnecter::connectMysql('share_story');
        $st = $db->query( $sql );
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $r  = $st->fetchAll();
        $r  = array_pop($r);
        
        if ($filed) {
            if (isset($r[$filed])) {
                return $r[$filed];
            } else {
                return '';
            }
        }
        return $r;
    }
    
    public function getStoryTotalCount($where = array())
    {
        $db = DbConnecter::connectMysql('share_story');
        if ($where) {
            $where = " where {$where} ";
        } else {
            $where = '';
        }
        $sql = "SELECT COUNT(*) FROM `story` {$where}";
        
        $st = $db->prepare($sql);
        $st->execute();
        $count = $st->fetch(PDO::FETCH_COLUMN);
        return $count;
    }
    
}

?>