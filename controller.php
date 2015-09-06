<?php
require dirname(__FILE__).'/init.php';
abstract class controller
{
	public function __construct()
	{
		$host = $_SERVER['HTTP_HOST'];
		if($host!='c.xiaoningmeng.com')
        {
        	header("HTTP/1.0 404 Not Found");
        	exit;
        }
		$this->checkFilters();
		$this->action();
	}

	public function checkFilters()
	{
	    $filters = $this->filters();
	    
	    $authLogin = @$filters['authLogin'];
	    $mPrivilegeObj = new ManagePrivilege();
	    $mPrivilegeObj->authLogin($authLogin);
	    
	    $privilege = @$filters['privilege'];
	    $actionData = $this->getActionData();
	    $mPrivilegeObj->checkPriv($privilege, $actionData, true);
	}
	
	public function filters()
	{
	    return array();
	}

	protected function getSmartyObj()
	{
		include_once SERVER_ROOT.'libs/smarty/Smarty.class.php';
		$smarty = new Smarty();
		$smarty->template_dir   = SERVER_ROOT."view/html/";
		$smarty->compile_dir 	= SERVER_ROOT."view/templates_c/";
		$smarty->cache_dir   = SERVER_ROOT."view/cache/";
		return $smarty;
	}
	protected function getUid()
	{
        $ssoObj = new Sso();
        $uid = $ssoObj->getUid();
        return $uid;
	}
	
	abstract  function action();
	
	public function getActionData()
	{
	    $script = str_replace('.php', '', @$_SERVER['SCRIPT_NAME']);
	    $scriptArr = @explode('/', trim($script, '/'));
	    if (!is_array($scriptArr)){
	        return array();
	    }
	    @list($module, $action) = $scriptArr;
	    $data['module'] = $module;
	    $data['action'] = $action;
	
	    $querys = $_SERVER["QUERY_STRING"];
	    $params = array();
	    if (!empty($querys)){
	        $queryParts = explode('&', $querys);
	        foreach ($queryParts as $param)
	        {
	            if($param=="")
	            {
	                continue;
	            }
	            $item = explode('=', $param);
	            $params[$item[0]] = $item[1];
	        }
	    }
	    $data['params'] = $params;
	
	    $this->actionData = $data;
	
	    return $data;
	}
	
	protected function showErrorJson($data)
	{
	    if(empty($data))
	    {
	        $data = ErrorConf::systemError();
	    }
	    echo json_encode($data);
	    exit;
	}
	protected function showSuccJson($data=array())
	{
		if(empty($data))
		{
			echo json_encode(array('code'=>10000));
		}else{
			echo json_encode(array('code'=>10000,'data'=>$data));
		}
		exit;
	}
	
	public function getRequest($option, $default='', $method='request')
	{
	    if ($method == 'get'){
	        return isset($_GET[$option]) ? $_GET[$option] : $default;
	    } else if ($method == 'post'){
	        return isset($_POST[$option]) ? $_POST[$option] : $default;
	    } else{
	        return isset($_REQUEST[$option]) ? $_REQUEST[$option] : $default;
	    } 
	}
	
	protected function redirect($url, $statusCode = 302)
	{
	    if(strpos($url,'/')===0 && strpos($url,'//')!==0) {
	        if(isset($_SERVER['HTTP_HOST'])) {
	            $hostInfo = 'http://'.$_SERVER['HTTP_HOST'];
	        } else {
	            $hostInfo = 'http://'.$_SERVER['SERVER_NAME'];
	        }
	        $url = $hostInfo . $url;
	    }
	    header('Location: ' . $url, true, $statusCode);
	}
}