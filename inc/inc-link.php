<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
require_once("$phpinc/ckinc/hash.php");
require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/colour.php");
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/appdynamics/core.php");
require_once("inc/inc-secret.php");

class cLinkPageData{
	public $page = null;
	public $credentials;
	public $time_window = null;
}

class cLinkPage{
	const GO_QS = "go";

	//***************************************************************************************
	private static function pr__get_referrer_key(){
		global $_SERVER;
		
		//-- check referrer
		$sReferrer = cHeader::get_referer();
	
		//-- get the unique key for the hash
		$oCred = new cAppDynCredentials();
		$oTimes = cRender::get_times();

		$sKey = $sReferrer."#H".$oCred->host."#U".$oCred->username."#S".$oTimes->start."#E".$oTimes->end;
		
		return $sKey;
	}
	
	//***************************************************************************************
	public static function get_link_id(){
		return cHash::hash( self::pr__get_referrer_key());
	}
	
	//***************************************************************************************
	public static function get_obj($psLinkID){
		$oObj = cHash::get_obj($psLinkID);
		if (get_class($oObj) !== "cLinkPageData"){
			cDebug::vardump($oObj,true);
			cDebug::error("not a valid link object");
		}
		$oObj->credentials->restricted_login = $oObj->page;
		return $oObj;
	}
	
	//***************************************************************************************
	//by the way because its a reversible hash - the site admin will find it hard to find 
	// hashes with passwords within them as there are going to be a large number of hashes to wade through
	public static function get_referrer_link(){
		$sKey = self::pr__get_referrer_key();
		cDebug::extra_debug("key is $sKey");
		
		//-- is there a objstore entry for the path?
		$oData = cHash::get($sKey);
		if ($oData == null){
			cDebug::write("creating hash object");

			$sReferrer = cHeader::get_referer();
			$oAppdynCredentials = new cAppDynCredentials();

			$oData = new cLinkPageData;
			$oData->page = $sReferrer;
			$oData->credentials = $oAppdynCredentials;
			$oData->time_window = cRender::get_times();
			
			cHash::put($sKey, $oData);
		}
		
		//--
		$link_ID = self::get_link_id();		
		$sUrl = cHeader::get_page_url()."?".self::GO_QS."=$link_ID";
		return $sUrl;
	}
	

}
?>
