<?php
include_once '../controller.php';
class analyticslisten extends controller
{
	public function action()
	{
		$showflag = $this->getRequest('showflag');
		if (empty($showflag)) 
		{
			//fbl drb rjb dlu
			$showflag='fbl';
		}
		$stime = $this->getRequest('stime');
		$etime = $this->getRequest('etime');
		if (empty($etime))
		{
			$etime = date("Ymd",time());
		}
		if (empty($stime))
		{
			$stime = $showflag=='drb' ?  date("Ymd", time()-86400) : date("Ymd", time()-30*86400);
		}
		$timestrforshow = array();
		$timestrforshow[$stime] = date('Y-m-d',strtotime($stime));
		$timestrforshow[$etime] = date('Y-m-d',strtotime($etime));
		$showtype='listen';
		
		$analytics = new Analytics();
		$pn = $tl = $title = $xaxis = $tn = $fn = $topic = $comment = $msg = $digg = $friend = $stn = $etn = '';
		$elist = $slist = $eslist = array();
		
		switch ($showflag)
		{
			case 'fbl':
				$list = $analytics->getanalyticscount('topic',$stime,$etime);
				list($title,$xaxis,$pn,$tn) = $analytics->getechars_result($list);
				$titleflag = '主题发布量';
				$title = "['主题发布量']";
				break;
			case 'drb':
				$elist = $analytics->getanalyticscontrast($showtype,$etime."00",$etime."23");
				$slist = $analytics->getanalyticscontrast($showtype,$stime."00",$stime."23");
				list($title,$xaxis,$etn,$epn,$stn,$spn) = $analytics->getechars_contrastresult($elist,$slist);
				$title = "['$stime','$etime']";
				list($elist,$slist,$eslist) = $analytics->getdrbresult($elist,$slist); 
				break;
		}
		
// 		print_r($elist);
// 		print_r($slist);
// 		exit;

		$smartyobj = $this->getSmartyObj();
		$smartyobj->assign('showflag', $showflag);
		$smartyobj->assign('stime',$stime);
		$smartyobj->assign('etime',$etime);
		$smartyobj->assign('xaxis',$xaxis);
		$smartyobj->assign('list',array_reverse($list));
		$smartyobj->assign('elist',($elist));
		$smartyobj->assign('slist',	($slist));
		$smartyobj->assign('eslist',($eslist));
		$smartyobj->assign('title',$title);
		$smartyobj->assign('titleflag',$titleflag);
		$smartyobj->assign('timestrforshow',$timestrforshow);
		$smartyobj->assign('tn',$tn);
		$smartyobj->assign('stn',$stn);
		$smartyobj->assign('etn',$etn);
		$smartyobj->display('analytics/analyticslisten.html');
	}
}

new analyticslisten();
?>