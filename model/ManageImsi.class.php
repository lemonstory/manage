<?php
class ManageImsi extends ModelBase 
{
    public $DB_INSTANCE = 'share_main';
    public $USER_IMSI_TABLE_NAME = 'user_imsi_info';
    public $USER_ACTION_LOG_TABLE_NAME = 'user_imsi_action_log';
    
    public function getListByColumnSearch($column = '', $value = '', $currentPage = 1, $perPage = 50)
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
        $list = $resIds = array();
        $db = DbConnecter::connectMysql($this->DB_INSTANCE);
        $sql = "SELECT * FROM `{$this->USER_IMSI_TABLE_NAME}`";
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
    
        $sql .= " ORDER BY `uimid` DESC LIMIT {$offset}, {$perPage}";
        $st = $db->prepare($sql);
        $st->execute();
        $result = $st->fetchAll(PDO::FETCH_ASSOC);
    
        return $result;
    }
    
    
    public function getCountByColumnSearch($column = '', $value = '')
    {
        $statusWhere = "";
         
        $db = DbConnecter::connectMysql($this->DB_INSTANCE);
        $sql = "SELECT COUNT(*) FROM `{$this->USER_IMSI_TABLE_NAME}`";
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
    
    
    public function getActionLogListByColumnSearch($column = '', $value = '', $currentPage = 1, $perPage = 50)
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
        $list = $resIds = array();
        $actionlogobj = new ActionLog();
        $month = date("Ym");
        $tablename = $actionlogobj->getUserImsiActionLogTableName($month);
        $db = DbConnecter::connectMysql($this->DB_INSTANCE);
        $sql = "SELECT * FROM `{$tablename}`";
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
    
        $sql .= " ORDER BY `id` DESC LIMIT {$offset}, {$perPage}";
        $st = $db->prepare($sql);
        $st->execute();
        $result = $st->fetchAll(PDO::FETCH_ASSOC);
    
        return $result;
    }
    
    
    public function getActionLogCountByColumnSearch($column = '', $value = '')
    {
        $statusWhere = "";
        $actionlogobj = new ActionLog();
        $month = date("Ym");
        $tablename = $actionlogobj->getUserImsiActionLogTableName($month);
        
        $db = DbConnecter::connectMysql($this->DB_INSTANCE);
        $sql = "SELECT COUNT(*) FROM `{$tablename}`";
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
    
    
    public function getUserImsiListByUids($uids)
    {
        if (empty($uids)) {
            return array();
        }
        if (!is_array($uids)) {
            $uids = array($uids);
        }
        $residstr = "";
        foreach ($uids as $uid) {
            $residstr .= "'{$uid}',";
        }
        $residstr = rtrim($residstr, ",");
        
        $userimsiobj = new UserImsi();
        $restype = $userimsiobj->USER_IMSI_INFO_RESTYPE_UID;
        
        $db = DbConnecter::connectMysql("share_main");
        $sql = "select * from {$this->USER_IMSI_TABLE_NAME} where `resid` in ($residstr) and `restype` = ?";
        $st = $db->prepare ( $sql );
        $st->execute (array($restype));
        $dbData = $st->fetchAll(PDO::FETCH_ASSOC);
        $db = null;
        if (empty($dbData)) {
            return array();
        }
        $list = array();
        foreach ($dbData as $value) {
            $list[$value['resid']] = $value;
        }
        return $list;
    }
}

?>