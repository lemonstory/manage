<?php
class Analytics extends ModelBase
{
    public $DB_INSTANCE = 'share_analytics';
    
	//插入 countavg
	public function putcountavg($timeline, $colname, $pn, $tn)
	{
		$avgcount = round($tn/$pn,2);
		
		$db = DbConnecter::connectMysql($this->DB_INSTANCE);
		$sql = "SELECT count(1) as flag from countavg where timeline=?;";
		$st = $db->prepare($sql);
		$st->execute(array($timeline));
    	$list = $st->fetch(PDO::FETCH_ASSOC);
    	if ($list['flag']<1)
    	{
    		$sql = "insert into countavg(timeline,$colname) values(?,?)";
    		$st = $db->prepare($sql);
    		$flag = $st->execute(array($timeline,$avgcount));
    	}else {
    		$sql = "update countavg set $colname=? where timeline=?";
    		$st = $db->prepare($sql);
    		$flag = $st->execute(array($avgcount,$timeline));
    	}
    	return $flag;
	}
	
	
	//插入 countfav
	public function putanalyticsfav($timeline, $personnum, $favnum)
	{
		if($timeline==0 || $timeline=="")
		{
			return false;
		}
		if($personnum==0 || $favnum==0)
		{
			return false;
		}
		$db = DbConnecter::connectMysql($this->DB_INSTANCE);
		$sql = "replace into countfav values(?,?,?);";
		$st = $db->prepare($sql);
		$flag = $st->execute(array($timeline,$personnum,$favnum));
     	$this->putcountavg($timeline,'favavg',$personnum,$favnum);
    	return $flag;
	}
	
	
	//插入 dayfav
	public function putanalyticsfavday($timeline, $personnum, $favnum)
	{
		if($timeline==0 || $timeline=="")
		{
			return false;
		}
		if($personnum==0 || $favnum==0)
		{
			return false;
		}
		$db = DbConnecter::connectMysql($this->DB_INSTANCE);
		$sql = "replace into dayfav values(?,?,?);";
		$st = $db->prepare($sql);
		$flag = $st->execute(array($timeline,$personnum,$favnum));
		return $flag;
	}
	
	
	//插入 countlisten
	public function putanalyticslisten($timeline, $personnum, $listennum)
	{
	    if($timeline==0 || $timeline=="")
	    {
	        return false;
	    }
	    if($personnum==0 || $listennum==0)
	    {
	        return false;
	    }
	    $db = DbConnecter::connectMysql($this->DB_INSTANCE);
	    $sql = "replace into countlisten values(?,?,?);";
	    $st = $db->prepare($sql);
	    $flag = $st->execute(array($timeline,$personnum,$listennum));
	    $this->putcountavg($timeline,'listenavg',$personnum,$listennum);
	    return $flag;
	}
	
	
	//插入 daylisten
	public function putanalyticslistenday($timeline, $personnum, $listennum)
	{
	    if($timeline==0 || $timeline=="")
	    {
	        return false;
	    }
	    if($personnum==0 || $listennum==0)
	    {
	        return false;
	    }
	    $db = DbConnecter::connectMysql($this->DB_INSTANCE);
	    $sql = "replace into daylisten values(?,?,?);";
	    $st = $db->prepare($sql);
	    $flag = $st->execute(array($timeline,$personnum,$listennum));
	    return $flag;
	}
	
	
	//插入 countdown
	public function putanalyticsdown($timeline, $personnum, $downnum)
	{
	    if($timeline==0 || $timeline=="")
	    {
	        return false;
	    }
	    if($personnum==0 || $downnum==0)
	    {
	        return false;
	    }
	    $db = DbConnecter::connectMysql($this->DB_INSTANCE);
	    $sql = "replace into countdown values(?,?,?);";
	    $st = $db->prepare($sql);
	    $flag = $st->execute(array($timeline,$personnum,$downnum));
	    $this->putcountavg($timeline,'downavg',$personnum,$downnum);
	    return $flag;
	}
	
	
	//插入 daydown
	public function putanalyticsdownday($timeline, $personnum, $downnum)
	{
	    if($timeline==0 || $timeline=="")
	    {
	        return false;
	    }
	    if($personnum==0 || $downnum==0)
	    {
	        return false;
	    }
	    $db = DbConnecter::connectMysql($this->DB_INSTANCE);
	    $sql = "replace into daydown values(?,?,?);";
	    $st = $db->prepare($sql);
	    $flag = $st->execute(array($timeline,$personnum,$downnum));
	    return $flag;
	}
	
	
	//插入 countcomment
	public function putanalyticscomment($timeline, $personnum, $commentnum)
	{
		if($timeline==0 || $timeline=="")
		{
			return false;
		}
		if($personnum==0 || $commentnum==0)
		{
			return false;
		}
		$db = DbConnecter::connectMysql($this->DB_INSTANCE);
		$sql = "replace into countcomment values(?,?,?);";
		$st = $db->prepare($sql);
		$flag = $st->execute(array($timeline,$personnum,$commentnum));
		$this->putcountavg($timeline,'commentavg',$personnum,$commentnum);
		return $flag;
	}
	
	
	//插入 daycomment
	public function putanalyticscommentday($timeline, $personnum, $commentnum, $actionalbumnum)
	{
		if($timeline==0 || $timeline=="")
		{
			return false;
		}
		if($personnum==0 || $commentnum==0 || $actionalbumnum==0)
		{
			return false;
		}
		$db = DbConnecter::connectMysql($this->DB_INSTANCE);
		$sql = "replace into daycomment values(?,?,?,?);";
		$st = $db->prepare($sql);
		$flag = $st->execute(array($timeline,$personnum,$commentnum,$actionalbumnum));
		return $flag;
	}
	
	
	//插入 countpassport
	public function putanalyticspassport($timeline, $personnum)
	{
		if($timeline==0 || $timeline=="")
		{
			return false;
		}
		if($personnum==0)
		{
			return false;
		}
		$db = DbConnecter::connectMysql($this->DB_INSTANCE);
		$sql = "replace into countreg values(?,?);";
		$st = $db->prepare($sql);
		$flag = $st->execute(array($timeline,$personnum));
		$this->putcountavg($timeline,'regavg',$personnum,$personnum);
		return $flag;
	}
	
	
	//获取发布量统计数据
    public function getanalyticscount($showtype, $stime='', $etime='')
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
    public function getanalyticscountrast($showtype, $stime, $etime)
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
    
    
    //获取关注数量
    /* public function getfollowdata($stime,$etime)
    {
    	$sql = "select  timeline as tl, totalnum as tn, fansnum as pn, follownum as fn  from dayfollowdata where timeline>=? and timeline<=?;";
    	$list = array();
    	$db = DbConnecter::connectMysql($this->DB_INSTANCE);
    	$st = $db->prepare($sql);
    	$st->execute(array($stime,$etime));
    	$list = $st->fetchAll(PDO::FETCH_ASSOC);
    	return $list;
    } */

}
?>