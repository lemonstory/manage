<?php
class ManageSystem extends ModelBase
{
    public $FOCUS_LINKTYPE_HTTP = 'http';
    public $FOCUS_LINKTYPE_ALBUM = 'album';
    public $FOCUS_LINKTYPE_LIST = array('http', 'album');
    
	
	public function getFocusInfo($focusid) 
	{
	    $db = DbConnecter::connectMysql('share_manage');
	    $sql = "SELECT * FROM `focus` WHERE `id` = ?";
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
	public function addFocusDb($linktype, $linkurl, $ordernum)
	{
		if (empty($linktype) || empty($linkurl) || !in_array($linktype, $this->FOCUS_LINKTYPE_LIST)) {
			return false;
		}
		
		$status = $this->RECOMMEND_STATUS_OFFLINE;
		$addtime = date("Y-m-d H:i:s");
	    if (empty($ordernum)) {
		    $ordernum = 100;
	    }
		$db = DbConnecter::connectMysql('share_manage');
        $sql = "INSERT INTO `focus` (`covertime`, `linktype`, `linkurl`, `ordernum`, `status`, `addtime`) VALUES (?, ?, ?, ?, ?, ?)";
        $st = $db->prepare($sql);
        $result = $st->execute(array(time(), $linktype, $linkurl, $ordernum, $status, $addtime));
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
	    $setstr = rtrim($setstr, ",");
	    
	    $db = DbConnecter::connectMysql('share_manage');
	    $sql = "UPDATE `focus` SET {$setstr} WHERE `id` = '{$focusid}'";
	    $st = $db->prepare($sql);
	    $result = $st->execute();
	    if (empty($result)) {
	        return false;
	    }
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
	     
	    $db = DbConnecter::connectMysql('share_manage');
	    $sql = "DELETE FROM `focus` WHERE `id` IN ({$focusids})";
	    $st = $db->prepare($sql);
	    $result = $st->execute();
	    if (empty($result)) {
	        return false;
	    }
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
	    
	    $db = DbConnecter::connectMysql('share_main');
	    $sql = "DELETE FROM `recommend_hot` WHERE `albumid` IN ({$albumids})";
	    $st = $db->prepare($sql);
	    $result = $st->execute();
	    if (empty($result)) {
	        return false;
	    }
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
	     
	    $db = DbConnecter::connectMysql('share_main');
	    $sql = "DELETE FROM `recommend_new_online` WHERE `albumid` IN ({$albumids})";
	    $st = $db->prepare($sql);
	    $result = $st->execute();
	    if (empty($result)) {
	        return false;
	    }
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
	     
	    $db = DbConnecter::connectMysql('share_main');
	    $sql = "DELETE FROM `recommend_same_age` WHERE `albumid` IN ({$albumids})";
	    $st = $db->prepare($sql);
	    $result = $st->execute();
	    if (empty($result)) {
	        return false;
	    }
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
		$db = DbConnecter::connectMysql('share_main');
        $sql = "INSERT INTO `recommend_hot` (`albumid`, `ordernum`, `status`, `addtime`) VALUES (?, ?, ?, ?)";
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
	    $db = DbConnecter::connectMysql('share_main');
	    $sql = "INSERT INTO `recommend_new_online` (`albumid`, `agetype`, `ordernum`, `status`, `addtime`) VALUES (?, ?, ?, ?, ?)";
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
	    $db = DbConnecter::connectMysql('share_main');
	    $sql = "INSERT INTO `recommend_same_age` (`albumid`, `agetype`, `ordernum`, `status`, `addtime`) VALUES (?, ?, ?, ?, ?)";
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
	 * @param unknown_type $dbinstance
	 * @param unknown_type $tablename
	 * @param unknown_type $column
	 * @param unknown_type $value
	 * @param unknown_type $currentPage
	 * @param unknown_type $perPage
	 * @return Ambiguous
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
            $sql .= " ORDER BY `status` ASC, `ordernum` ASC, `id` ASC LIMIT {$offset}, {$perPage}";
        } else {
            $sql .= " ORDER BY `status` ASC, `ordernum` ASC, `albumid` ASC LIMIT {$offset}, {$perPage}";
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
    
    public function updateRecommendInfoByIds($dbinstance, $tablename, $ids, $data)
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
    
        $db = DbConnecter::connectMysql($dbinstance);
        $sql = "UPDATE `{$tablename}` SET {$setstr} WHERE `albumid` IN ({$idStr})";
        $st = $db->prepare($sql);
        $result = $st->execute();
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
        $db = DbConnecter::connectMysql('share_main');
        $sql = "SELECT * FROM `recommend_hot` WHERE `albumid` = ?";
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
        $db = DbConnecter::connectMysql('share_main');
        $sql = "SELECT * FROM `recommend_new_online` WHERE `albumid` = ?";
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
        $db = DbConnecter::connectMysql('share_main');
        $sql = "SELECT * FROM `recommend_same_age` WHERE `albumid` = ?";
        $st = $db->prepare($sql);
        $st->execute(array($albumid));
        $info = $st->fetch(PDO::FETCH_ASSOC);
        if (empty($info)) {
            return false;
        }
        return true;
    }
}