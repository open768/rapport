<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
require_once("$phpinc/ckinc/colour.php");
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/http.php");
require_once("$appdlib/appdynamics.php");
require_once("$appdlib/core.php");


//#######################################################################
//#######################################################################
class cRenderObjs{
	//**************************************************************************
	private static $oAppDCredentials = null;
	
	
	//**************************************************************************
	public static function get_appd_credentials(){
		cDebug::enter();
		$oCred = self::$oAppDCredentials;
		if (!$oCred){
			cDebug::extra_debug("got credentials");
			$oCred = new cAppDynCredentials;
			$oCred->check();
			self::$oAppDCredentials = $oCred;
		}
		cDebug::leave();;
		return $oCred;
	}
	
	//***************************************************************************
	public static function make_app_obj($psApp, $psAID){
		return new cAppDApp($psApp, $psAID);		
	}
	
	public static function make_tier_obj($poApp, $psTier, $psTID){
		return new cAppDTier($poApp, $psTier, $psTID);
	}
	
	public static function make_trans_obj($poTier, $psTrans, $psTrID){
		return new cAppDTrans($poTier, $psTrans, $psTrID);
	}
	
	//***************************************************************************
	public static function get_current_app(){
		$sApp = cHeader::get(cRender::APP_QS);
		$sAID = cHeader::get(cRender::APP_ID_QS);
		return self::make_app_obj($sApp, $sAID);
	}

	public static function get_current_tier(){
		$oApp = self::get_current_app();
		$sTier = cHeader::get(cRender::TIER_QS);
		$sTID = cHeader::get(cRender::TIER_ID_QS);
		return self::make_tier_obj($oApp, $sTier, $sTID);
	}
	
	public static function get_current_trans(){
		$oTier = self::get_current_tier();
		$trans = cHeader::get(cRender::TRANS_QS);
		$trid = cHeader::get(cRender::TRANS_ID_QS);
		return self::make_trans_obj($oTier, $trans, $trid);
	}
}
?>
