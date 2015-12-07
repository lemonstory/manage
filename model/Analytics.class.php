<?php
class Analytics extends ModelBase
{
    public $DB_INSTANCE = 'share_analytics';
    
	
	/**
	 * 记录所有统计类型的平均数
	 * @param S $timeline    计算的日期，如某天的统计结果"20151101" 或 某小时的统计结果"2015110120"
	 * @param S $colname     数据类型：如收藏favavg
	 * @param I $pn          计算的uid人数
	 * @param I $tn          计算的数据总量
	 * @return boolean
	 */
	public function putCountAvg($timeline, $colname, $pn, $tn)
	{
		$avgcount = round($tn/$pn,2);
		
		$db = DbConnecter::connectMysql($this->DB_INSTANCE);
		$sql = "SELECT count(1) as flag from `countavg` where `timeline` = ?";
		$st = $db->prepare($sql);
		$st->execute(array($timeline));
    	$list = $st->fetch(PDO::FETCH_ASSOC);
    	if ($list['flag']<1)
    	{
    		$sql = "insert into `countavg` (`timeline`,`{$colname}`) values(?, ?)";
    		$st = $db->prepare($sql);
    		$flag = $st->execute(array($timeline, $avgcount));
    	}else {
    		$sql = "update `countavg` set `{$colname}` = ? where `timeline` = ?";
    		$st = $db->prepare($sql);
    		$flag = $st->execute(array($avgcount, $timeline));
    	}
    	return $flag;
	}
	
	
	/**
	 * 记录当前该小时的收藏人数、收藏总量
	 * @param S $timeline     指定日期，天或小时
	 * @param I $personnum    收藏人数
	 * @param I $favnum       收藏量
	 * @return boolean
	 */
	public function putAnalyticsFavHour($timeline, $personnum, $favnum)
	{
		if(empty($timeline))
		{
			return false;
		}
		if(empty($favnum))
		{
			return false;
		}
		$db = DbConnecter::connectMysql($this->DB_INSTANCE);
		$sql = "replace into `countfav` (`timeline`, `personnum`, `favnum`) values (?, ?, ?)";
		$st = $db->prepare($sql);
		$flag = $st->execute(array($timeline, $personnum, $favnum));
		//$this->putCountAvg($timeline, 'favavg', $personnum, $favnum);
    	return $flag;
	}
	
	
	/**
	 * 记录当天的收藏人数、收藏总量
	 * @param S $timeline
	 * @param I $personnum
	 * @param I $favnum
	 * @return boolean
	 */
	public function putAnalyticsFavDay($timeline, $personnum, $favnum)
	{
	    if(empty($timeline))
		{
			return false;
		}
		if(empty($personnum) || empty($favnum))
		{
			return false;
		}
		$db = DbConnecter::connectMysql($this->DB_INSTANCE);
		$sql = "replace into `dayfav` (`timeline`, `personnum`, `totalnum`) values(?, ?, ?)";
		$st = $db->prepare($sql);
		$flag = $st->execute(array($timeline, $personnum, $favnum));
		return $flag;
	}
	
	
	//插入 countlisten
	public function putAnalyticsListenHour($timeline, $personnum, $listennum)
	{
	    if(empty($timeline))
		{
			return false;
		}
		if(empty($listennum))
		{
			return false;
		}
	    $db = DbConnecter::connectMysql($this->DB_INSTANCE);
	    $sql = "replace into `countlisten` (`timeline`, `personnum`, `listennum`) values(?, ?, ?)";
	    $st = $db->prepare($sql);
	    $flag = $st->execute(array($timeline, $personnum, $listennum));
	    //$this->putCountAvg($timeline, 'listenavg', $personnum, $listennum);
	    return $flag;
	}
	
	
	//插入 daylisten
	public function putAnalyticsListenDay($timeline, $personnum, $listennum)
	{
	    if(empty($timeline))
		{
			return false;
		}
		if(empty($personnum) || empty($listennum))
		{
			return false;
		}
	    $db = DbConnecter::connectMysql($this->DB_INSTANCE);
	    $sql = "replace into `daylisten` (`timeline`, `personnum`, `totalnum`) values(?, ?, ?)";
	    $st = $db->prepare($sql);
	    $flag = $st->execute(array($timeline, $personnum, $listennum));
	    return $flag;
	}
	
	
	//插入 countdown
	public function putAnalyticsDownHour($timeline, $personnum, $downnum)
	{
	    if(empty($timeline))
		{
			return false;
		}
		if(empty($downnum))
		{
			return false;
		}
	    $db = DbConnecter::connectMysql($this->DB_INSTANCE);
	    $sql = "replace into `countdown` (`timeline`, `personnum`, `downnum`) values(?, ?, ?)";
	    $st = $db->prepare($sql);
	    $flag = $st->execute(array($timeline, $personnum, $downnum));
	    //$this->putCountAvg($timeline, 'downavg', $personnum, $downnum);
	    return $flag;
	}
	
	
	//插入 daydown
	public function putAnalyticsDownDay($timeline, $personnum, $downnum)
	{
	    if(empty($timeline))
		{
			return false;
		}
		if(empty($personnum) || empty($downnum))
		{
			return false;
		}
	    $db = DbConnecter::connectMysql($this->DB_INSTANCE);
	    $sql = "replace into `daydown` (`timeline`, `personnum`, `totalnum`) values(?, ?, ?)";
	    $st = $db->prepare($sql);
	    $flag = $st->execute(array($timeline, $personnum, $downnum));
	    return $flag;
	}
	
	
	//插入 countcomment
	public function putAnalyticsCommentHour($timeline, $personnum, $commentnum)
	{
	    if(empty($timeline))
		{
			return false;
		}
		if(empty($commentnum))
		{
			return false;
		}
		$db = DbConnecter::connectMysql($this->DB_INSTANCE);
		$sql = "replace into `countcomment` (`timeline`, `personnum`, `commentnum`) values(?, ?, ?)";
		$st = $db->prepare($sql);
		$flag = $st->execute(array($timeline, $personnum, $commentnum));
		//$this->putCountAvg($timeline, 'commentavg', $personnum, $commentnum);
		return $flag;
	}
	
	
	//插入 daycomment
	public function putAnalyticsCommentDay($timeline, $personnum, $commentnum, $actionalbumnum)
	{
	    if(empty($timeline))
		{
			return false;
		}
		if(empty($personnum) || empty($commentnum) || empty($actionalbumnum))
		{
			return false;
		}
		$db = DbConnecter::connectMysql($this->DB_INSTANCE);
		$sql = "replace into `daycomment` (`timeline`, `personnum`, `totalnum`, `actionalbumnum`) values(?, ?, ?, ?)";
		$st = $db->prepare($sql);
		$flag = $st->execute(array($timeline, $personnum, $commentnum, $actionalbumnum));
		return $flag;
	}
	
	
	//插入 countreg
	public function putAnalyticsPassportHour($timeline, $personnum)
	{
	    if(empty($timeline))
		{
			return false;
		}
		if(empty($personnum))
		{
			return false;
		}
		$db = DbConnecter::connectMysql($this->DB_INSTANCE);
		$sql = "replace into `countreg` (`timeline`, `personnum`) values(?, ?)";
		$st = $db->prepare($sql);
		$flag = $st->execute(array($timeline, $personnum));
		//$this->putCountAvg($timeline, 'regavg', $personnum, $personnum);
		return $flag;
	}
	
	//插入 daycomment
	public function putAnalyticsPassportDay($timeline, $personnum)
	{
	    if(empty($timeline))
	    {
	        return false;
	    }
	    if(empty($personnum))
	    {
	        return false;
	    }
	    $db = DbConnecter::connectMysql($this->DB_INSTANCE);
	    $sql = "replace into `dayreg` (`timeline`, `personnum`) values(?, ?)";
	    $st = $db->prepare($sql);
	    $flag = $st->execute(array($timeline, $personnum));
	    return $flag;
	}
	
	
	//获取n天内统计列表数据
    public function getAnalyticsDayList($showtype, $stime='', $etime='')
    {
        if (empty($showtype)) {
            return array();
        }
        $stime = date("Ymd", strtotime($stime));
        $etime = date("Ymd", strtotime($etime));
        
    	$list = array();
    	$db = DbConnecter::connectMysql($this->DB_INSTANCE);
		switch ($showtype)
		{
		    case 'fav' :
		        $sql = "select * from dayfav where timeline >= ? and timeline <= ?";
		        break;
			case 'listen' :
				$sql = "select * from daylisten where timeline >= ? and timeline <= ?";
				break;
			case 'comment' :
				$sql = "select * from daycomment where timeline >= ? and timeline <= ?";
				break;
			case 'reg' :
				$sql = "select * from dayreg where timeline >= ? and timeline <= ?";
				break;
			case 'down' :
				$sql = "select * from daydown where timeline >= ? and timeline <= ?";
				break;
			case 'countavg' :
				$sql = "select avg(favavg) as fav, avg(commentavg) as comment, avg(listenavg) as listen, avg(downavg) as down, avg(regavg) as reg, substring(timeline,1,8) as tl from countavg where substring(timeline,1,8)>=? and substring(timeline,1,8)<=? group by tl;";
				break;
		}
    	$st = $db->prepare($sql);
    	$st->execute(array($stime, $etime));
    	$list = $st->fetchAll(PDO::FETCH_ASSOC);
    	return $list;
    }

}
?>