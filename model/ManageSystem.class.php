<?php
class ManageSystem extends ModelBase
{
	/**
	 * 首页获取焦点图列表
	 * @param I $len
	 * @return 
	 */
	public function getFocusList($len = 5)
	{
		if (empty($len)) {
			$len = 5;
		}
		
		$db = DbConnecter::connectMysql('share_manage');
        $sql = "SELECT * FROM `focus` WHERE `status` = '{$this->RECOMMEND_STATUS_ONLIINE}' ORDER BY `ordernum` DESC LIMIT $len";
        $st = $db->prepare($sql);
        $st->execute();
        $list = $st->fetchAll(PDO::FETCH_ASSOC);
        return $list;
	}
	
	
	/**
	 * 首页获取热门推荐列表
	 * @param I $len
	 * @return array
	 */
	public function getRecommendHotList($len = 5)
	{
		if (empty($len)) {
			$len = 5;
		}
		
		$db = DbConnecter::connectMysql('share_main');
        $sql = "SELECT * FROM `recommend_hot` WHERE `status` = '{$this->RECOMMEND_STATUS_ONLIINE}' ORDER BY `ordernum` DESC LIMIT $len";
        $st = $db->prepare($sql);
        $st->execute();
        $list = $st->fetchAll(PDO::FETCH_ASSOC);
        return $list;
	}
	
	
	/**
	 * 首页最新上架的上线列表
	 * 按照年龄段，展示最新上架的故事专辑
	 * @param I $babyagetype
	 * @param I $len
	 * @return array
	 */
	public function getNewOnlineList($babyagetype = 0, $len = 5)
	{
		if (!empty($babyagetype) && !in_array($babyagetype, $this->AGE_TYPE_LIST)) {
			$this->setError(ErrorConf::paramError());
			return array();
		}
		$start = 0;
		if (empty($len)) {
			$len = 5;
		}
		
		$status = $this->RECOMMEND_STATUS_ONLIINE; // 已上线状态
		$where = "`status` = '{$status}'";
		if (!empty($babyagetype)) {
			$where .= " AND `agetype` = '{$babyagetype}'";
		}
		$db = DbConnecter::connectMysql('share_main');
		$sql = "SELECT * FROM `recommend_new_online` 
				WHERE {$where} ORDER BY `ordernum` DESC LIMIT $len";
		$st = $db->prepare($sql);
		$st->execute();
		$list = $st->fetchAll(PDO::FETCH_ASSOC);
		if (empty($list)) {
			return array();
		}
		return $list;
	}
	
	
	/**
	 * 后台添加焦点图
	 * @param S $content
	 * @param I $ordernum
	 * @param S $addtime
	 * @return boolean
	 */
	public function addFocusDb($content, $ordernum)
	{
		if (empty($content) || empty($ordernum)) {
			return false;
		}
		$status = 0;
		$addtime = date("Y-m-d H:i:s");
		$db = DbConnecter::connectMysql('share_manage');
        $sql = "INSERT INTO `focus` (`content`, `ordernum`, `status`, `addtime`) VALUES (?, ?, ?, ?)";
        $st = $db->prepare($sql);
        $result = $st->execute(array($content, $ordernum, $status, $addtime));
        if (empty($result)) {
            return false;
        }
        return true;
	}
	
	
	/**
	 * 后台编辑推荐，热门专辑数据
	 * @param I $albumid
	 * @param I $ordernum
	 * @return bool
	 */
	public function addRecommendHotDb($albumid, $ordernum)
	{
		if (empty($albumid) || empty($ordernum)) {
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
	 * 后台推荐列表、查询列表
	 * @param unknown_type $dbinstance
	 * @param unknown_type $tablename
	 * @param unknown_type $column
	 * @param unknown_type $value
	 * @param unknown_type $currentPage
	 * @param unknown_type $perPage
	 * @return Ambiguous
	 */
	public function getRecommendListByColumnSearch($dbinstance, $tablename, $column = '', $value = '', $currentPage = 1, $perPage = 50)
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
        
        $sql .= " ORDER BY `ordernum` DESC LIMIT {$offset}, {$perPage}";
        $st = $db->prepare($sql);
        $st->execute();
        $result = $st->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    
	public function getRecommendCountByColumnSearch($dbinstance, $tablename, $column = '', $value = '')
    {
        $statusWhere = "";
    	
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
    
    public function updateRecommendStatusByIds($dbinstance, $tablename, $ids, $status)
    {
        if (empty($ids) || !in_array($status, $this->AGE_TYPE_LIST)) {
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
    
        $db = DbConnecter::connectMysql($dbinstance);
        $sql = "UPDATE `{$tablename}` SET `status` = ? WHERE `albumid` IN ({$idStr})";
        $st = $db->prepare($sql);
        $result = $st->execute(array($status));
        return $result;
    }
    
    
    /**
     * 检测指定专辑是否已经在热门推荐表中
     * @param I $albumid
     * @return boolean    true/false    存在/不存在
     */
    private function checkRecommendHotIsExist($albumid)
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
    private function checkNewOnlineIsExist($albumid)
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
}