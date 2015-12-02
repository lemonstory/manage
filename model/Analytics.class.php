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
		$this->putCountAvg($timeline, 'favavg', $personnum, $favnum);
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
	    $this->putCountAvg($timeline, 'listenavg', $personnum, $listennum);
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
	    $this->putCountAvg($timeline, 'downavg', $personnum, $downnum);
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
		return $flag;
	}
	
	
	//插入 daycomment
	public function putAnalyticsCommentDay($timeline, $personnum, $commentnum, $actionalbumnum)
	{
	    if(empty($timeline))
		{
			return false;
		}
		if(empty($personnum) || empty($listennum) || empty($actionalbumnum))
		{
			return false;
		}
		$db = DbConnecter::connectMysql($this->DB_INSTANCE);
		$sql = "replace into `daycomment` (`timeline`, `personnum`, `totalnum`, `actionalbumnum`) values(?, ?, ?, ?, ?)";
		$st = $db->prepare($sql);
		$flag = $st->execute(array($timeline, $personnum, $commentnum, $actionalbumnum));
		$this->putCountAvg($timeline, 'commentavg', $personnum, $commentnum);
		return $flag;
	}
	
	
	//插入 countpassport
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
		$this->putCountAvg($timeline, 'regavg', $personnum, $personnum);
		return $flag;
	}
	
	
	//获取发布量统计数据
    public function getAnalyticsCount($showtype, $stime='', $etime='')
    {
        if (empty($showtype)) {
            return array();
        }
        
    	$list = array();
    	$db = DbConnecter::connectMysql($this->DB_INSTANCE);
		switch ($showtype)
		{
		    case 'fav' :
		        $sql = "select sum(personnum) as pn, sum(favnum) as tn,     substring(timeline,1,8) as tl from countfav where substring(timeline,1,8)>=? and substring(timeline,1,8)<=? group by tl;";
		        break;
			case 'listen' :
				$sql = "select sum(personnum) as pn, sum(listennum) as tn,  substring(timeline,1,8) as tl from countlisten where substring(timeline,1,8)>=? and substring(timeline,1,8)<=? group by tl;";
				break;
			case 'comment' :
				$sql = "select sum(personnum) as pn, sum(commentnum) as tn, substring(timeline,1,8) as tl from countcomment where substring(timeline,1,8)>=? and substring(timeline,1,8)<=? group by tl;";
				break;
			case 'reg' :
				$sql = "select sum(personnum) as pn, sum(personnum) as tn,  substring(timeline,1,8) as tl from countreg where substring(timeline,1,8)>=? and substring(timeline,1,8)<=? group by tl;";
				break;
			case 'down' :
				$sql = "select sum(personnum) as pn, sum(personnum) as tn,  substring(timeline,1,8) as tl from countdown where substring(timeline,1,8)>=? and substring(timeline,1,8)<=? group by tl;";
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
    
    
    //获取两个单日对比数据
    public function getAnalyticsCountRast($showtype, $stime, $etime)
    {
    	$list = array();
    	$db = DbConnecter::connectMysql($this->DB_INSTANCE);
    	switch ($showtype)
    	{
    	    case 'fav' : 
    	        $sql = "select  personnum as pn, topicnum as tn, substring(timeline,9,2) as tl from counttopicvideo where timeline>=? and timeline<=? limit 25 ;";
    	        break;
    		case 'listen' :
    			$sql = "select  personnum as pn, topicnum as tn, substring(timeline,9,2) as tl from counttopic where timeline>=? and timeline<=? limit 25 ;";
    			break;
    		case 'comment' :
    			$sql = "select  personnum as pn, commentnum as tn, substring(timeline,9,2) as tl from countcomment where timeline>=? and timeline<=? limit 25 ;";
    			break;
    		case 'reg' :
    			$sql = "select  personnum as pn, personnum as tn, substring(timeline,9,2) as tl from countreg where timeline>=? and timeline<=? limit 25 ;";
    			break;
    		case 'down' :
    			$sql = "select  personnum as tn, substring(timeline,9,2) as tl from countdown where timeline>=? and timeline<=? limit 25 ;";
    			break;
    		case 'countavg' :
    			$sql = "select * from countavg where timeline>=? and timeline<=? limit 25 ;";
    			break;
    	}
    	$st = $db->prepare($sql);
    	$st->execute(array($stime, $etime));
    	$list = $st->fetchAll(PDO::FETCH_ASSOC);
    	return $list;
    }

}
?>