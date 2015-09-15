<?php
include_once '../controller.php';
class analyticsreg extends controller
{
	public function action()
	{
		$showflag = $this->getRequest('showflag');
		if (empty($showflag)) {
			/*
			 * fbl    一段天数内的注册量曲线图
			 * drb    2个单日对比图
			 */
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
		$showtype = 'reg';
		
		$analytics = new Analytics();
		$pn = $tl = $title = $xaxis = $tn = $fn = $topic = $comment = $stn = $etn = '';
		$elist = $slist = $eslist = array();
		
		switch ($showflag)
		{
			case 'fbl':
				$list = $analytics->getanalyticscount($showtype,$stime,$etime);
				list($title,$xaxis,$pn,$tn) = $analytics->getechars_result($list);
				$titleflag = '注册量';
				$title = "['注册量']";
				break;
			case 'drb':
				$elist = $analytics->getanalyticscontrast($showtype,$etime."00",$etime."23");
				$slist = $analytics->getanalyticscontrast($showtype,$stime."00",$stime."23");
				list($title,$xaxis,$etn,$epn,$stn,$spn) = $analytics->getechars_contrastresult($elist,$slist);
				$title = "['$stime','$etime']";
				list($elist,$slist,$eslist) = $analytics->getdrbresult($elist,$slist); 
				break;
			
		}
		
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
		$smartyobj->display('analytics/analyticsreg.html');
	}
}

new analyticsreg();
?>