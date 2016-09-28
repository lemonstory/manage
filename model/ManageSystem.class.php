<?php
class ManageSystem extends ModelBase
{
    public $FOCUS_LINKTYPE_HTTP = 'http';
    public $FOCUS_LINKTYPE_ALBUM = 'album';
    public $FOCUS_LINKTYPE_LIST = array('http', 'album');
    
    public $MAIN_DB_INSTANCE = 'share_main';
    public $MANAGE_DB_INSTANCE = 'share_manage';
    public $RECOMMEND_HOT_TABLE_NAME = 'recommend_hot';
    public $RECOMMEND_SAME_AGE_TABLE_NAME = 'recommend_same_age';
    public $RECOMMEND_NEW_ONLINE_TABLE_NAME = 'recommend_new_online';
    public $FOCUS_TABLE_NAME = 'focus';
    
	
	public function getFocusInfo($focusid) 
	{
	    $db = DbConnecter::connectMysql($this->MANAGE_DB_INSTANCE);
	    $sql = "SELECT * FROM `{$this->FOCUS_TABLE_NAME}` WHERE `id` = ?";
	    $st = $db->prepare($sql);
	    $st->execute(array($focusid));
	    $info = $st->fetch(PDO::FETCH_ASSOC);
	    if (empty($info)) {
	        return array();
	    }
	    return $info;
	}
	
	/**
	 * 后台添加焦点图
	 * @param S $linktype    链接跳转方式：http/album
	 * @param S $linkurl
	 * @return boolean
	 */
	public function addFocusDb($linktype, $linkurl, $ordernum,$categoryEnName=0)
	{
		if (empty($linktype) || empty($linkurl) || !in_array($linktype, $this->FOCUS_LINKTYPE_LIST) || empty($categoryEnName)) {
			return false;
		}
		
		$status = $this->RECOMMEND_STATUS_OFFLINE;
		$addtime = date("Y-m-d H:i:s");
	    if (empty($ordernum)) {
		    $ordernum = 100;
	    }
		$db = DbConnecter::connectMysql($this->MANAGE_DB_INSTANCE);
        $sql = "INSERT INTO `{$this->FOCUS_TABLE_NAME}` (`covertime`, `linktype`, `linkurl`, `ordernum`, `status`, `category`, `addtime`) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $st = $db->prepare($sql);
        $result = $st->execute(array(time(), $linktype, $linkurl, $ordernum, $status, $categoryEnName, $addtime));
        if (empty($result)) {
            return false;
        }
        return $db->lastInsertId();
	}
	
	public function updateFocusInfo($focusid, $data)
	{
	    if (empty($focusid) || empty($data)) {
	        return false;
	    }
	    
	    $setstr = "";
	    if (!empty($data['covertime'])) {
	        $setstr .= "`covertime` = '{$data['covertime']}',";
	    }
	    if (!empty($data['picid'])) {
	        $setstr .= "`picid` = '{$data['picid']}',";
	    }
	    if (!empty($data['linktype']) && in_array($data['linktype'], $this->FOCUS_LINKTYPE_LIST)) {
	        $setstr .= "`linktype` = '{$data['linktype']}',";
	    }
	    if (!empty($data['linkurl'])) {
	        $setstr .= "`linkurl` = '{$data['linkurl']}',";
	    }
	    if (!empty($data['ordernum'])) {
	        $setstr .= "`ordernum` = '{$data['ordernum']}',";
	    }
	    if (!empty($data['status'])) {
	        $setstr .= "`status` = '{$data['status']}',";
	    }
		if (!empty($data['category'])) {
			$setstr .= "`category` = '{$data['category']}',";
		}
	    $setstr = rtrim($setstr, ",");
	    
	    $db = DbConnecter::connectMysql($this->MANAGE_DB_INSTANCE);
	    $sql = "UPDATE `{$this->FOCUS_TABLE_NAME}` SET {$setstr} WHERE `id` = '{$focusid}'";
	    $st = $db->prepare($sql);
	    $result = $st->execute();
	    if (empty($result)) {
	        return false;
	    }
	    
	    // 清除列表cache
	    $cacheobj = new CacheWrapper();
	    $cacheobj->deleteNSCache($this->FOCUS_TABLE_NAME);
	    return true;
	}
	
	public function delFocusDb($focusids)
	{
	    if (empty($focusids)) {
	        return false;
	    }
	    if (!is_array($focusids)) {
	        $focusids = array($focusids);
	    }
	    $focusids = implode(",", $focusids);
	     
	    $db = DbConnecter::connectMysql($this->MANAGE_DB_INSTANCE);
	    $sql = "DELETE FROM `{$this->FOCUS_TABLE_NAME}` WHERE `id` IN ({$focusids})";
	    $st = $db->prepare($sql);
	    $result = $st->execute();
	    if (empty($result)) {
	        return false;
	    }
	    
	    // 清除列表cache
	    $cacheobj = new CacheWrapper();
	    $cacheobj->deleteNSCache($this->FOCUS_TABLE_NAME);
	    return true;
	}
	
	public function delHotRecommendByAlbumId($albumids)
	{
	    if (empty($albumids)) {
	        return false;
	    }
	    if (!is_array($albumids)) {
	        $albumids = array($albumids);
	    }
	    $albumids = implode(",", $albumids);
	    
	    $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
	    $sql = "DELETE FROM `{$this->RECOMMEND_HOT_TABLE_NAME}` WHERE `albumid` IN ({$albumids})";
	    $st = $db->prepare($sql);
	    $result = $st->execute();
	    if (empty($result)) {
	        return false;
	    }
	    
	    // 清除列表cache
	    $cacheobj = new CacheWrapper();
	    $cacheobj->deleteNSCache($this->RECOMMEND_HOT_TABLE_NAME);
	    return true;
	}
	
	public function delNewOnlineByAlbumId($albumids)
	{
	    if (empty($albumids)) {
	        return false;
	    }
	    if (!is_array($albumids)) {
	        $albumids = array($albumids);
	    }
	    $albumids = implode(",", $albumids);
	     
	    $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
	    $sql = "DELETE FROM `{$this->RECOMMEND_NEW_ONLINE_TABLE_NAME}` WHERE `albumid` IN ({$albumids})";
	    $st = $db->prepare($sql);
	    $result = $st->execute();
	    if (empty($result)) {
	        return false;
	    }
	    
	    // 清除列表cache
	    $cacheobj = new CacheWrapper();
	    $cacheobj->deleteNSCache($this->RECOMMEND_NEW_ONLINE_TABLE_NAME);
	    return true;
	}
	
	public function delSameAgeByAlbumId($albumids)
	{
	    if (empty($albumids)) {
	        return false;
	    }
	    if (!is_array($albumids)) {
	        $albumids = array($albumids);
	    }
	    $albumids = implode(",", $albumids);
	     
	    $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
	    $sql = "DELETE FROM `{$this->RECOMMEND_SAME_AGE_TABLE_NAME}` WHERE `albumid` IN ({$albumids})";
	    $st = $db->prepare($sql);
	    $result = $st->execute();
	    if (empty($result)) {
	        return false;
	    }
	    
	    // 清除列表cache
	    $cacheobj = new CacheWrapper();
	    $cacheobj->deleteNSCache($this->RECOMMEND_SAME_AGE_TABLE_NAME);
	    return true;
	}
	
	
	public function delRankListenUser($uid)
	{
	    if (empty($uid)) {
	        return false;
	    }
	    $rankkey = RedisKey::getRankListenUserKey();
	    $redisobj = AliRedisConnecter::connRedis("rank");
	    return $redisobj->zDelete($rankkey, $uid);
	}
	
	
	/**
	 * 后台编辑推荐，热门专辑数据
	 * @param I $albumid
	 * @param I $ordernum
	 * @return bool
	 */
	public function addRecommendHotDb($albumid, $ordernum = 100)
	{
		if (empty($albumid)) {
			$this->setError(ErrorConf::paramError());
			return false;
		}
		$check = $this->checkRecommendHotIsExist($albumid);
		if ($check) {
		    $this->setError(array('code'=>'100002','desc'=>'此专辑已经在热门推荐中存在'));
		    return false;
		}
		
		$status = $this->RECOMMEND_STATUS_OFFLINE;
		$addtime = date("Y-m-d H:i:s");
		$db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
        $sql = "INSERT INTO `{$this->RECOMMEND_HOT_TABLE_NAME}` (`albumid`, `ordernum`, `status`, `addtime`) VALUES (?, ?, ?, ?)";
        $st = $db->prepare($sql);
        $result = $st->execute(array($albumid, $ordernum, $status, $addtime));
        if (empty($result)) {
        	return false;
        }
        return true;
	}
	
	
	/**
	 * 抓取的新专辑，自动添加到最新上架数据表
	 * @param I $albumid
	 * @param I $agetype    专辑适合年龄段
	 * @param I $ordernum   排序
	 * @return bool
	 */
	public function addRecommendNewOnlineDb($albumid, $agetype, $ordernum = 100)
	{
	    if (empty($albumid)) {
	        $this->setError(ErrorConf::paramError());
	        return false;
	    }
	    $check = $this->checkNewOnlineIsExist($albumid);
	    if ($check) {
	        $this->setError(array('code'=>'100002','desc'=>'此专辑已经在最新上架中存在'));
	        return false;
	    }
	    
	    $status = $this->RECOMMEND_STATUS_OFFLINE;
	    $addtime = date("Y-m-d H:i:s");
	    $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
	    $sql = "INSERT INTO `{$this->RECOMMEND_NEW_ONLINE_TABLE_NAME}` (`albumid`, `agetype`, `ordernum`, `status`, `addtime`) VALUES (?, ?, ?, ?, ?)";
	    $st = $db->prepare($sql);
	    $result = $st->execute(array($albumid, $agetype, $ordernum, $status, $addtime));
	    if (empty($result)) {
	        return false;
	    }
	    return true;
	}
	
	
	public function addRecommendSameAgeDb($albumid, $agetype, $ordernum = 100)
	{
	    if (empty($albumid)) {
	        $this->setError(ErrorConf::paramError());
	        return false;
	    }
	    $check = $this->checkSameAgeIsExist($albumid);
	    if ($check) {
	        $this->setError(array('code'=>'100002','desc'=>'此专辑已经在同龄在听中存在'));
	        return false;
	    }
	     
	    $status = $this->RECOMMEND_STATUS_OFFLINE;
	    $addtime = date("Y-m-d H:i:s");
	    $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
	    $sql = "INSERT INTO `{$this->RECOMMEND_SAME_AGE_TABLE_NAME}` (`albumid`, `agetype`, `ordernum`, `status`, `addtime`) VALUES (?, ?, ?, ?, ?)";
	    $st = $db->prepare($sql);
	    $result = $st->execute(array($albumid, $agetype, $ordernum, $status, $addtime));
	    if (empty($result)) {
	        return false;
	    }
	    return true;
	}
	
	
	public function getRecommendInfoByFilter($dbinstance, $tablename, $where)
	{
	    $db = DbConnecter::connectMysql($dbinstance);
	    $sql = "SELECT * FROM `{$tablename}`";
	    if (!empty($where)) {
	        $sql .= " WHERE {$where}";
	    }
	    $st = $db->prepare($sql);
	    $st->execute();
	    $info = $st->fetch(PDO::FETCH_ASSOC);
	    if (empty($info)) {
	        return array();
	    }
	    return $info;
	}
	
	/**
	 * 后台推荐列表、查询列表
	 * @param S $dbinstance
	 * @param S $tablename
	 * @param S $column
	 * @param S $value
	 * @param I $currentPage
	 * @param I $perPage
	 * @return array
	 */
	public function getRecommendListByColumnSearch($dbinstance, $tablename, $column = '', $value = '', $status = 0, $currentPage = 1, $perPage = 50)
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
        $db = DbConnecter::connectMysql($dbinstance);
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
        
        if ($tablename == 'focus') {
            $sql .= " ORDER BY `ordernum` ASC, `status` ASC, `id` ASC LIMIT {$offset}, {$perPage}";
        } else {
            $sql .= " ORDER BY `ordernum` ASC, `status` ASC,`albumid` ASC LIMIT {$offset}, {$perPage}";
        }
        $st = $db->prepare($sql);
        $st->execute();
        $result = $st->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    
	public function getRecommendCountByColumnSearch($dbinstance, $tablename, $column = '', $value = '', $status = 0)
    {
        $statusWhere = "";
        if (!empty($status)) {
            $statusWhere = "`status` = {$status}";
        }
    	
        $db = DbConnecter::connectMysql($dbinstance);
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
    
    
    // 批量更新相关推荐列表的状态和排序
    public function updateHotRecommendInfoByIds($ids, $data)
    {
        if (empty($ids) || empty($data)) {
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
    
        $setstr = "";
        foreach ($data as $key => $value) {
            $setstr .= "`{$key}` = '{$value}',";
        }
        $setstr = rtrim($setstr, ",");
    
        $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
        $sql = "UPDATE `{$this->RECOMMEND_HOT_TABLE_NAME}` SET {$setstr} WHERE `albumid` IN ({$idStr})";
        $st = $db->prepare($sql);
        $result = $st->execute();
    
        // 清除列表cache
        $cacheobj = new CacheWrapper();
        $cacheobj->deleteNSCache($this->RECOMMEND_HOT_TABLE_NAME);
        return $result;
    }
    
    
    // 批量更新相关推荐列表的状态和排序
    public function updateNewOnlineInfoByIds($ids, $data)
    {
        if (empty($ids) || empty($data)) {
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
    
        $setstr = "";
        foreach ($data as $key => $value) {
            $setstr .= "`{$key}` = '{$value}',";
        }
        $setstr = rtrim($setstr, ",");
    
        $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
        $sql = "UPDATE `{$this->RECOMMEND_NEW_ONLINE_TABLE_NAME}` SET {$setstr} WHERE `albumid` IN ({$idStr})";
        $st = $db->prepare($sql);
        $result = $st->execute();
    
        // 清除列表cache
        $cacheobj = new CacheWrapper();
        $cacheobj->deleteNSCache($this->RECOMMEND_NEW_ONLINE_TABLE_NAME);
        return $result;
    }
    
    
    // 批量更新相关推荐列表的状态和排序
    public function updateSameAgeInfoByIds($ids, $data)
    {
        if (empty($ids) || empty($data)) {
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
        
        $setstr = "";
        foreach ($data as $key => $value) {
            $setstr .= "`{$key}` = '{$value}',";
        }
        $setstr = rtrim($setstr, ",");
        
        $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
        $sql = "UPDATE `{$this->RECOMMEND_SAME_AGE_TABLE_NAME}` SET {$setstr} WHERE `albumid` IN ({$idStr})";
        $st = $db->prepare($sql);
        $result = $st->execute();
        
        // 清除列表cache
        $cacheobj = new CacheWrapper();
        $cacheobj->deleteNSCache($this->RECOMMEND_SAME_AGE_TABLE_NAME);
        return $result;
    }
    
    
    /**
     * 检测指定专辑是否已经在热门推荐表中
     * @param I $albumid
     * @return boolean    true/false    存在/不存在
     */
    public function checkRecommendHotIsExist($albumid)
    {
        if (empty($albumid)) {
            $this->setError(ErrorConf::paramError());
            return false;
        }
        $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
        $sql = "SELECT * FROM `{$this->RECOMMEND_HOT_TABLE_NAME}` WHERE `albumid` = ?";
        $st = $db->prepare($sql);
        $st->execute(array($albumid));
        $info = $st->fetch(PDO::FETCH_ASSOC);
        if (empty($info)) {
            return false;
        }
        return true;
    }
    
    
    /**
     * 检测指定专辑是否已经在最新上架表中
     * @param I $albumid
     * @return boolean    true/false    存在/不存在
     */
    public function checkNewOnlineIsExist($albumid)
    {
        if (empty($albumid)) {
            $this->setError(ErrorConf::paramError());
            return false;
        }
        $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
        $sql = "SELECT * FROM `{$this->RECOMMEND_NEW_ONLINE_TABLE_NAME}` WHERE `albumid` = ?";
        $st = $db->prepare($sql);
        $st->execute(array($albumid));
        $info = $st->fetch(PDO::FETCH_ASSOC);
        if (empty($info)) {
            return false;
        }
        return true;
    }
    
    
    /**
     * 检测指定专辑是否已经在同龄在听表中
     * @param I $albumid
     * @return boolean    true/false    存在/不存在
     */
    public function checkSameAgeIsExist($albumid)
    {
        if (empty($albumid)) {
            $this->setError(ErrorConf::paramError());
            return false;
        }
        $db = DbConnecter::connectMysql($this->MAIN_DB_INSTANCE);
        $sql = "SELECT * FROM `{$this->RECOMMEND_SAME_AGE_TABLE_NAME}` WHERE `albumid` = ?";
        $st = $db->prepare($sql);
        $st->execute(array($albumid));
        $info = $st->fetch(PDO::FETCH_ASSOC);
        if (empty($info)) {
            return false;
        }
        return true;
    }
}