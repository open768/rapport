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
require_once("$phpinc/ckinc/common.php");
require_once("$appdlib/common.php");
require_once("$appdlib/core.php");


//#################################################################
//# 
//#################################################################
class cAppDynAccountData{
	public $value;
	public $date;
	
	function  __construct($psDate, $psValue){
		$this->date = $psDate;
		$this->value = $psValue;
	}
}

class cAppDynAccount{
	public static $account_id = null;
	
	//*****************************************************************
	public static function GET_account_id(){
		cDebug::enter();

		if (!self::$account_id){
			cAppDynCore::$URL_PREFIX="/api/accounts";
			cAppDynCore::$CONTROLLER_PREFIX=null;
			
			$oJson = cAppDynCore::GET("/myaccount?");
			self::$account_id = $oJson->id;
			
			cDebug::write("accountID is ".self::$account_id);
		}

		cDebug::leave();
		return self::$account_id;
	}

	//*****************************************************************
	public static function GET_license_modules(){
		cDebug::enter();

		$oJson = self::pr__get("/licensemodules?");

		cDebug::leave();
		return $oJson;
	}
	

	//*****************************************************************
	//dates must be of format 2015-12-25T00:00:00Z
	public static function GET_license_usage($psModule, $piMonths=1){
		cDebug::enter();
		
		cDebug::write("looking for usage of $psModule for $piMonths months");
		
		$dStart = date(cAppDynCore::DATE_FORMAT, time()-($piMonths*cCommon::SECONDS_IN_MONTH));
		$dEnd = date(cAppDynCore::DATE_FORMAT, time());

		$oJson = self::pr__get("/licensemodules/$psModule/usages?startdate=$dStart&enddate=$dEnd");
		
		$aUsages = [];
		if ($oJson && property_exists($oJson,"usages")){
			//cDebug::vardump($oJson);
			foreach ($oJson->usages as $oData)
				$aUsages[] = new cAppDynAccountData($oData->createdOnIsoDate, $oData->maxUnitsUsed);
		}
		
		cDebug::leave();
		return $aUsages;
	}
	
	//*****************************************************************
	private static function pr__get($psURLAdd){
		cDebug::enter();
		$sID = self::GET_account_id();
		
		cAppDynCore::$URL_PREFIX="/api/accounts/$sID";
		cAppDynCore::$CONTROLLER_PREFIX=null;
		$oJson = cAppDynCore::GET($psURLAdd);

		
		cDebug::leave();
		return $oJson;
	}
}

?>
