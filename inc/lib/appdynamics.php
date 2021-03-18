<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//see 
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/cached_http.php");
require_once("$phpinc/pubsub/pub-sub.php");
require_once("$appdlib/demo.php");
require_once("$appdlib/common.php");
require_once("$appdlib/auth.php");
require_once("$appdlib/core.php");
require_once("$appdlib/account.php");
require_once("$appdlib/util.php");
require_once("$appdlib/metrics.php");
require_once("$appdlib/controllerui.php");
require_once("$appdlib/restui.php");

require_once("$appdlib/controller.php");
require_once("$appdlib/app.php");
require_once("$appdlib/tier.php");
require_once("$appdlib/trans.php");

//#################################################################
//# 
//#################################################################
class cTransExtCalls{
    public $trans1, $trans2, $calls, $times;
}

class cAppdObj{
	public $application;
	public $tier;
	public $business_transaction;
	public $backend;
	public $metric_name;
	public $data = null;
}

class cAppdMetricLeaf{
	public $tier = null;
	public $name = null;
	public $metric = null;
	public $notes = null;
	public $children = [];
	
	public function has_children(){
		return count($this->children);
	}
	
	public function get_matching_names($psFragment, &$aOutput){
		if (strstr($this->name,$psFragment))
			$aOutput[] = $this;
		
		foreach ($this->children as $oChild)
			$oChild->get_matching_names($psFragment, $aOutput);
	}
	
	public function add_child($oChild){
		$this->children[] = $oChild;
	}
}

class cAppDDetails extends cAppdObj{
   public $name, $id, $calls, $times, $type;
   function __construct($psName, $psId, $poCalls, $poTimes) {
		$this->name = $psName;
		$this->id = $psId;
		$this->calls = $poCalls;
		$this->times = $poTimes;
   }
}


cAppDApp::$null_app = new cAppDApp(null,null);
cAppDApp::$db_app = new cAppDApp(cAppDynCore::DATABASE_APPLICATION,cAppDynCore::DATABASE_APPLICATION);




//#################################################################
//# CLASSES
//#################################################################
class cAppDynWebsite{
	const DOWNLOAD_URL = "https://download.appdynamics.com/download/downloadfile/?apm=jvm%2Cdotnet%2Cphp%2Cmachine%2Cwebserver%2Cdb%2Cappd4db%2Canalytics%2Cios%2Candroid%2Ccpp-sdk%2Cpython%2Cnodejs%2Cgolang-sdk%2Cuniversal-agent%2Ciot%2Cnetviz&eum=linux%2Cosx%2Cwindows%2Cgeoserver%2Cgeodata%2Csynthetic&events=linuxwindows&format=json&os=linux%2Cosx%2Cwindows&platform_admin_os=linux%2Cosx%2Cwindows";
	public static function GET_latest_downloads(){
		$oHttp = new cCachedHttp();
		$oHttp->USE_CURL = false;
		$sUrl = self::DOWNLOAD_URL;
		$aData = [];
		while ($sUrl){
			$oData = $oHttp->getCachedJson($sUrl);
			if ($oData->count >0){
				$sUrl = $oData->next;
				foreach ($oData->results as $oDownload)
					$aData[] = $oDownload;
			}else
				$sUrl = null;
		}
		
		uasort($aData,"ad_sort_downloads");
		return $aData;
	}
}

//#################################################################
//# CLASSES
//#################################################################
class cAppDyn{
	const APPDYN_LOGO = 'adlogo.jpg';
	const APPDYN_OVERFLOWING_BT = "_APPDYNAMICS_DEFAULT_TX_";
	const ALL_EVENT_TYPES = "POLICY_OPEN_CRITICAL,POLICY_OPEN_WARNING,POLICY_CLOSE,POLICY_CLOSE_CRITICAL,POLICY_CLOSE_WARNING,POLICY_CONTINUES_CRITICAL";
	const ALL_SEVERITIES = "WARN,ERROR,INFO";
	
	private static $maAppNodes = null;
	
	
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//* All
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	
	
	public static function is_demo(){
		$oCred = new cAppDynCredentials();
		$oCred->check();
		return $oCred->is_demo();
	}
	
		
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//* Databases
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	
	public static function GET_Database_ServerStats($psDB){
		$sMetricPath= cAppDynMetric::databaseServerStats($psDB);
		return  cAppdynCore::GET_Metric_heirarchy(cAppDynCore::DATABASE_APPLICATION, $sMetricPath, false);
	}
				
}
?>
