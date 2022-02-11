<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
require_once("$phpinc/ckinc/colour.php");
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/http.php");
require_once("$ADlib/AD.php");
require_once("$ADlib/core.php");


//#######################################################################
//#######################################################################
class cRenderObjs{
	//**************************************************************************
	private static $oAppDCredentials = null;
	
	
	//**************************************************************************
	public static function get_AD_credentials(){
		//cDebug::enter();
		$oCred = self::$oAppDCredentials;
		if ($oCred == null){
			cDebug::extra_debug("getting credentials");
			try{
				$oCred = new cADCredentials;
				$oCred->check();
			}
			catch (Exception $e){
				$oCred = null;
			}
			self::$oAppDCredentials = $oCred;
		}
		//cDebug::leave();;
		return $oCred;
	}
	
	//***************************************************************************
	public static function make_app_obj($psApp, $psAID){
		return new cADApp($psApp, $psAID);		
	}
	
	public static function make_tier_obj($poApp, $psTier, $psTID){
		return new cADTier($poApp, $psTier, $psTID);
	}
	
	
	//***************************************************************************
	public static function get_current_app(){
		cDebug::enter();
		$sApp = cHeader::get(cRenderQS::APP_QS);
		$sAID = cHeader::get(cRenderQS::APP_ID_QS);
		$oApp = null;
		if (!$sApp && !$sAID)
			cDebug::extra_debug_warning("no current app");
		else
			$oApp = self::make_app_obj($sApp, $sAID);
		cDebug::leave();
		
		return $oApp;
	}

	public static function get_current_tier(){
		cDebug::enter();
		$oObj = null;
		$oApp = self::get_current_app();
		if ($oApp){
			$sTier = cHeader::get(cRenderQS::TIER_QS);
			$sTID = cHeader::get(cRenderQS::TIER_ID_QS);
			if (!$sTier && !$sTID)
				cDebug::extra_debug_warning("no current tier");
			else
				$oObj = self::make_tier_obj($oApp, $sTier, $sTID);
		}
		cDebug::leave();
		return $oObj;
	}
	
	public static function get_current_trans(){
		$oTier = self::get_current_tier();
		$sTrans = cHeader::get(cRenderQS::TRANS_QS);
		$sTrid = cHeader::get(cRenderQS::TRANS_ID_QS);
		return new cADTrans($oTier, $sTrans, $sTrid);
	}
	
	public static function get_current_snapshot(){
		$oTrans = self::get_current_trans();
		$sGuuid = cHeader::get(cRenderQS::SNAP_GUID_QS);
		$sStartTime = cHeader::get(cRenderQS::SNAP_TIME_QS);
		return new cADSnapshot($oTrans, $sGuuid, $sStartTime);
	}
}
?>
