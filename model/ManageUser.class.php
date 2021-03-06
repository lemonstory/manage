<?php
class ManageUser extends ModelBase 
{
    public function getUserList($currentPage = 1, $perPage = 50) 
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
        $offset = ($currentPage - 1) * $perPage;
        
        $list = array();
        $db = DbConnecter::connectMysql('share_main');
        $sql = "SELECT * FROM `user_info` ORDER BY `uid` DESC LIMIT {$offset}, {$perPage}";
        
        $st = $db->prepare($sql);
        $st->execute();
        $list = $st->fetchAll(PDO::FETCH_ASSOC);
        return $list;
    }
    
    public function getUserTotalCount()
    {
        $db = DbConnecter::connectMysql('share_main');
        $sql = "SELECT COUNT(*) FROM `user_info`";
        
        $st = $db->prepare($sql);
        $st->execute();
        $count = $st->fetch(PDO::FETCH_COLUMN);
        return $count;
    }
    
    //TODO: 这部分代码是shit
    public function getListByColumnSearch($column = '', $value = '', $status = 0, $currentPage = 1, $perPage = 50,$db = "share_manage",$table="system_user_info")
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
        $offset = ($currentPage - 1) * $perPage;

        $statusWhere = "";
        if (!empty($status)) {
            $statusWhere = "`status` = {$status}";
        }
        $list = $resIds = array();
        $db = DbConnecter::connectMysql($db);
        $sql = "SELECT * FROM `{$table}`";
        if (!empty($column)) {
            if ($column == 'nickname') {
                $sql .= " WHERE `{$column}` like '%$value%'";
            } else {
                $sql .= " WHERE `{$column}` = '$value'";
            }
            if (!empty($statusWhere)) {
                $sql .= " AND {$statusWhere}";
            }
        } else {
            if (!empty($statusWhere)) {
                $sql .= " WHERE {$statusWhere}";
            }
        }

        $sql .= " ORDER BY `uid` DESC LIMIT {$offset}, {$perPage}";
        $st = $db->prepare($sql);
        $st->execute();
        $result = $st->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }


    public function getCountByColumnSearch($column = '', $value = '', $status = 0,$db = "share_manage",$table="system_user_info")
    {
        $statusWhere = "";
        if (!empty($status)) {
            $statusWhere = "`status` = {$status}";
        }

        $db = DbConnecter::connectMysql($db);
        $sql = "SELECT COUNT(*) FROM `$table`";
        if (!empty($column)) {
            $sql .= " WHERE `{$column}` = '$value'";
            if (!empty($statusWhere)) {
                $sql .= " AND {$statusWhere}";
            }
        } else {
            if (!empty($statusWhere)) {
                $sql .= " WHERE {$statusWhere}";
            }
        }
        
        $st = $db->prepare($sql);
        $st->execute();
        $count = $st->fetch(PDO::FETCH_COLUMN);
        return $count;
    }
}

?>