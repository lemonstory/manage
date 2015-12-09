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
	
	public function getSameAgeListByColumnSearch($column = '', $value = '', $status = 0, $currentPage = 1, $perPage = 50)
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
        
        $sql .= " ORDER BY `status` ASC, `ordernum` ASC, `albumid` ASC LIMIT {$offset}, {$perPage}";
        $st = $db->prepare($sql);
        $st->execute();
        $result = $st->fetchAll(PDO::FETCH_ASSOC);
    
        return $result;
    }
    
    
	public function getSameAgeCountByColumnSearch($column = '', $value = '', $status = 0)
    {
        $statusWhere = "";
        if (!empty($status)) {
            $statusWhere = "`status` = {$status}";
        }
    	
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
    
    
    /**
     * 收听次数的用户排行榜
     */
    public function getRankUserListenListBySearch($uid = 0, $currentPage = 1, $perPage = 50)
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
        $start = ($currentPage - 1) * $perPage;
        $end = $start + $perPage - 1;
        
        $list = array();
        $rankkey = RedisKey::getRankListenUserKey();
        $redisobj = AliRedisConnecter::connRedis("rank");
        if (empty($uid)) {
            $list = $redisobj->zRevRange($rankkey, $start, $end, true);
        } else {
            $num = $redisobj->zScore($rankkey, $uid);
            $list = array($uid => $num);
        }
        return $list;
    }
    public function getRankUserListenCountBySearch($uid = 0)
    {
        if (empty($uid)) {
            $rankkey = RedisKey::getRankListenUserKey();
            $redisobj = AliRedisConnecter::connRedis("rank");
            $count = $redisobj->zSize($rankkey);
        } else {
            $count = 1;
        }
        
        return $count;
    }
}