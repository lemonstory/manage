<?php
//error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('PRC');
define("SERVER_ROOT", dirname(__FILE__)."/");

if (DIRECTORY_SEPARATOR == '/'){
    define("API_TUTUIM_ROOT", dirname(SERVER_ROOT)."/api.tutuim.com/");
} else {
    define("API_TUTUIM_ROOT", dirname(dirname(dirname(SERVER_ROOT)))."/com.tutuim.api/src/trunk/");
}


//加载本项目CONFIG后台api.tutuim.com的config
include_once API_TUTUIM_ROOT."/config/config.php";
include_once API_TUTUIM_ROOT."/config/dbconf.php";
include_once API_TUTUIM_ROOT."/config/cacheconf.php";
include_once API_TUTUIM_ROOT."/libs/functions.php";
include_once  API_TUTUIM_ROOT."/config/redisconf.php";
include API_TUTUIM_ROOT."/config/kvstoreconf.php";
//autoload本项目MODEL和api.tutuim.com的model
/**
 * autoload : SERVER_ROOT.[model/lib]
 * usage : new oss_sdk() => include('lib/oss/sdk.class.php');new oss_sdk();
 */
function __autoload($className){
	$className = (str_replace("_", DIRECTORY_SEPARATOR, $className));

	$incFile = SERVER_ROOT."model/$className.class.php";
	if (file_exists($incFile)){
		include_once $incFile;
		return;
	}
	
	$incFile = SERVER_ROOT."libs/$className.class.php";
	if (file_exists($incFile)){
		include_once $incFile;
		return;
	}
	
	$incFile = API_TUTUIM_ROOT."model/$className.class.php";
	if (file_exists($incFile)){
	    include_once $incFile;
	    return;
	}
	
	$incFile = API_TUTUIM_ROOT."libs/$className.class.php";
	if (file_exists($incFile)){
	    include_once $incFile;
	    return;
	}
	
}
