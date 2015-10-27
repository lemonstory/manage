<?php
class ManageListen extends ModelBase
{
	public $MAIN_DB_INSTANCE = 'share_main';
	public $RECOMMEND_SAME_AGE_TABLE_NAME = 'recommend_same_age';
	public $LISTEN_STORY_TABLE_NAME = 'listen_story';
	
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
	    $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
	    $sql = "SELECT * FROM `{$this->LISTEN_STORY_TABLE_NAME}`";
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
	
	    $sql .= " ORDER BY `uptime` DESC LIMIT {$offset}, {$perPage}";
	    $st = $db->prepare($sql);
	    $st->execute();
	    $result = $st->fetchAll(PDO::FETCH_ASSOC);
	
	    return $result;
	}
	
	
	public function getCountByColumnSearch($column = '', $value = '')
	{
	    $statusWhere = "";
	     
	    $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
	    $sql = "SELECT COUNT(*) FROM `{$this->LISTEN_STORY_TABLE_NAME}`";
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
	
	public function getSameAgeListByColumnSearch($column = '', $value = '', $currentPage = 1, $perPage = 50)
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
        $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
        $sql = "SELECT * FROM `{$this->RECOMMEND_SAME_AGE_TABLE_NAME}`";
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
        
        $sql .= " ORDER BY `ordernum` DESC LIMIT {$offset}, {$perPage}";
        $st = $db->prepare($sql);
        $st->execute();
        $result = $st->fetchAll(PDO::FETCH_ASSOC);
    
        return $result;
    }
    
    
	public function getSameAgeCountByColumnSearch($column = '', $value = '')
    {
        $statusWhere = "";
    	
        $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
        $sql = "SELECT COUNT(*) FROM `{$this->RECOMMEND_SAME_AGE_TABLE_NAME}`";
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
    
    public function updateSameAgeStatusByIds($ids, $status)
    {
    	$listenobj = new Listen();
        if (empty($ids) || !in_array($status, in_array($listenobj->RECOMMEND_STATUS_OFFLINE, $listenobj->RECOMMEND_STATUS_ONLIINE))) {
            return false;
        }
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $idStr = "";
        foreach ($ids as $id) {
            $idStr .= $id . ",";
        }
        $idStr = rtrim($idStr, ",");
    
        $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
        $sql = "UPDATE `{$this->RECOMMEND_SAME_AGE_TABLE_NAME}` SET `status` = ? WHERE `albumid` IN ({$idStr})";
        $st = $db->prepare($sql);
        $result = $st->execute(array($status));
        return $result;
    }
}