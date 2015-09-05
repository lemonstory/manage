<?php
class ManageListen extends ModelBase
{
	public $MAIN_DB_INSTANCE = 'share_main';
	public $RECOMMEND_SAME_AGE_TABLE_NAME = 'recommend_same_age';
	
	/**
	 * 获取同龄在听的所有列表
	 * @param I $status
	 * @param I $babyagetype
	 * @param I $len
	 * @return array
	 */
	/*public function getSameAgeList($status = 0, $babyagetype = 0, $currentPage = 1, $perPage = 50)
	{
		$listenobj = new Listen();
		if (empty($babyagetype) || !in_array($babyagetype, $listenobj->AGE_TYPE_LIST)) {
			$this->setError(ErrorConf::paramError());
			return array();
		}
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
        
		$where = "";
		if (!empty($status)) {
			$where .= "`status` = '{$status}'";
		}
		if (!empty($babyagetype)) {
			$where .= " AND `agetype` = '{$babyagetype}'";
		}
		
		$db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
		$sql = "SELECT * FROM {$this->RECOMMEND_SAME_AGE_TABLE_NAME} 
				WHERE {$where} ORDER BY `ordernum` DESC LIMIT $offset, $perPage";
		$st = $db->prepare($sql);
		$st->execute(array($status, $babyagetype));
		$list = $st->fetchAll(PDO::FETCH_ASSOC);
		if (empty($list)) {
			return array();
		}
		return $list;
	}*/
	
	
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