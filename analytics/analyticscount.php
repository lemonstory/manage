<?php
include_once '../controller.php';
class analyticscount extends controller
{
	public function action()
	{
		$showtype = $this->getRequest('showtype');
		if (empty($showtype)) 
		{
			$showtype='topic';
		}
		$stime = $this->getRequest('stime');
		$etime = $this->getRequest('etime');
		if (empty($etime))
		{
			$etime = date("Ymd",time());
		}
		if (empty($stime))
		{
			$stime = date("Ymd", time()-30*86400);
		}

		$analytics = new Analytics();
		$list = $analytics->getanalyticscount($showtype,$stime,$etime);
// 		print_r($list);
// 		exit;
		//定义赋值数据
		$pn = $tl = $title = $xaxis = $tn = $fn = $topic = $comment = $msg = $digg = $friend = '';
		switch ($showtype)
		{
			case "friend":
				list($title,$xaxis,$pn,$tn,$fn) = $analytics->getechars_friendresult($list);
				break;
			case "countavg":
				list($title,$xaxis,$topic,$comment,$msg,$digg,$friend) = $analytics->getechars_countavgresult($list);
				break;
			default:
				list($title,$xaxis,$pn,$tn) = $analytics->getechars_result($list);
				break;
		}
		foreach($list as $k=>$one)
		{
			$list[$k]['topic'] = round($one['topic'],2);
			$list[$k]['comment'] = round($one['comment'],2);
			$list[$k]['msg'] = round($one['msg'],2);
			$list[$k]['digg'] = round($one['digg'],2);
			$list[$k]['friend'] = round($one['friend'],2);
		}
		$smartyobj = $this->getSmartyObj();
		$smartyobj->assign('showtype', $showtype);
		$smartyobj->assign('stime',$stime);
		$smartyobj->assign('etime',$etime);
		$smartyobj->assign('xaxis',$xaxis);
		$smartyobj->assign('list',array_reverse($list));
		
		
		
		//其他数据
		$smartyobj->assign('title',$title);
		$smartyobj->assign('pn',$pn);
		$smartyobj->assign('tn',$tn);
		$smartyobj->assign('fn',$fn);
		//人均走势比数据
		$smartyobj->assign('topic',$topic);
		$smartyobj->assign('comment',$comment);
		$smartyobj->assign('msg',$msg);
		$smartyobj->assign('digg',$digg);
		$smartyobj->assign('friend',$friend);
		
		$smartyobj->display('analytics/analyticscount.html');
	}
}

new analyticscount();
?>