<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//see 
require_once("$appdlib/appdynamics.php");

//#################################################################
//# CLASSES
//#################################################################
class cAppDynController{		
	//****************************************************************
	public static function GET_Controller_version(){
		$aConfig = self::GET_configuration();
		foreach ($aConfig as $oItem)
			if ($oItem->name === "schema.version"){
				$sVersion = preg_replace("/^0*/","",$oItem->value);
				$sVersion = preg_replace("/-0+(\d+)/",'.$1',$sVersion);
				$sVersion = preg_replace("/-0+/",'.0',$sVersion);
				return $sVersion;
			}
	}

	//****************************************************************
	public static function GET_configuration(){
		$old_prefix = cAppDynCore::$URL_PREFIX;
		cAppDynCore::$URL_PREFIX = cAppDynCore::CONFIG_METRIC_PREFIX ;
		$oData = cAppDynCore::GET("?");
		cAppDynCore::$URL_PREFIX = $old_prefix ;
		return $oData;
	}
	
	//*****************************************************************
	public static function GET_Applications(){
		if ( cAppDyn::is_demo()) return cAppDynDemo::GET_Applications();
		
		$aData = cAppDynCore::GET('?');
		if ($aData)	uasort($aData,"ad_sort_by_name");
		
		$aOut = [];
		foreach ($aData as $oItem){
			$oApp = new cAppDApp($oItem->name, $oItem->id);
			$aOut[] = $oApp;
		}
		
		return ($aOut);		
	}
	
	//*****************************************************************
	public static function GET_Databases(){
		$sMetricPath= cAppDynMetric::databases();
		return  cAppdynCore::GET_Metric_heirarchy(cAppDynCore::DATABASE_APPLICATION, $sMetricPath, false);
	}

	//*****************************************************************
	public static function GET_allBackends(){
		$aServices = [];
		
		$oApps = self::GET_Applications();
		foreach ($oApps as $oApp){
			$aBackends = self::GET_Backends($oApp->name);
			foreach ($aBackends as $oBackend){
				$sBName = $oBackend->name;
				if (!isset($aServices[$sBName])) $aServices[$sBName] = [];
				$aServices[$sBName][] = new cAppDApp($oApp->name, $oApp->id);
			}
		}
		ksort($aServices);
		return $aServices;
	}

}
?>
