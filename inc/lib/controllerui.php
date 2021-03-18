<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2016 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//#################################################################
//# CLASSES
//#################################################################
class cAppDynControllerUI{
	private static function pr__get_location($psLocation){
		$sBaseUrl = cAppDynCore::GET_controller();
		return $sBaseUrl."/#/location=$psLocation";		
	}
	
	private static function pr__get_app_location($poApp, $psLocation){
		$sBaseUrl = self::pr__get_location($psLocation);
		return $sBaseUrl."&application=$poApp->id";		
	}

	private static function pr__get_tier_location($poTier, $psLocation){
		$sBaseUrl = self::pr__get_app_location($poTier->app, $psLocation);
		return $sBaseUrl."&component=$poTier->id";		
	}
	//###############################################################################################
	public static function home(){
		$sURL = self::pr__get_location("AD_HOME");
		return $sURL;
	}	
	private static function time_command($poTimes, $psKey="timeRange"){
		return  "$psKey=Custom_Time_Range.BETWEEN_TIMES.".$poTimes->end.".".$poTimes->start.".0";
	}

	//###############################################################################################
	public static function agents(){
		$sURL = self::pr__get_location("SETTINGS_AGENTS");
		return $sURL;

	}
	public static function licenses(){
		$sURL = self::pr__get_location("SETTINGS_LICENSE");	
		return $sURL;
	}
	
	//###############################################################################################
	public static function apps_home(){
		return self::pr__get_location("APPS_ALL_DASHBOARD");
	}
	public static function application($poApp){
		return self::pr__get_app_location($poApp, "APP_DASHBOARD");
	}
	public static function app_slow_transactions($poApp){
		return self::pr__get_app_location($poApp, "APP_SLOW_TRANSACTIONS");
	}
		
	//###############################################################################################
	//# Databases
	public static function databases(){
		return  self::pr__get_location("DB_MONITORING_SERVER_LIST");
	}
	
	//###############################################################################################
	//# Events
	public static function events($poApp){
		return self::pr__get_app_location($poApp, "APP_EVENTSTREAM_LIST");
	}

	public static function event_detail($piEventID){
		$sURL = self::pr__get_location("APP_EVENT_VIEWER_MODAL");	
		return $sURL."&eventSummary=$piEventID";
	}
	
	//###############################################################################################
	//# Nodes
	public static function nodes($poApp){
		$sURL = self::pr__get_app_location($poApp,"APP_INFRASTRUCTURE");
		return $sURL."&appServerListMode=grid";
	}

	public static function nodeDashboard($poApp, $piNodeID){
		$sURL = self::pr__get_app_location($poApp,"APP_NODE_MANAGER");
		return $sURL."&node=$piNodeID&dashboardMode=force";
	}
	
	public static function nodeAgent($poApp, $piNode){
		$sURL = self::pr__get_app_location($poApp, "APP_NODE_AGENTS");
		return $sURL."&bypassAssociatedLocationsCheck=true&tab=10&node=$piNode&memoryViewMode=0";
	}
	
	public static function machineDetails($piMachineID){
		$sURL = self::pr__get_location("INFRASTRUCTURE_MACHINE_DETAIL");	
		return $sURL."&machine=$piMachineID";
	}

	//###############################################################################################
	//# Remote services
	public static function remoteServices($poApp){
		return self::pr__get_app_location($poApp, "APP_BACKEND_LIST");
	}

	//###############################################################################################
	//# Tiers
	public static function tier_errors($poApp, $poTier){
		return self::pr__get_tier_location($poTier, "APP_TIER_ERROR_TRANSACTIONS");
	}
	
	public static function tier_slow_transactions($poApp, $poTier){
		return self::pr__get_tier_location($poTier, "APP_TIER_SLOW_TRANSACTIONS");
	}
	public static function tier_slow_remote($poApp, $poTier){
		return self::pr__get_tier_location($poTier, "APP_TIER_SLOW_DB_REMOTE_SERVICE_CALLS");
	}
	
	public static function tier($poApp, $poTier){
		$sURL = self::pr__get_tier_location($poTier, "APP_COMPONENT_MANAGER");
		return $sURL."&dashboardMode=force";
	}
	
	//###############################################################################################
	//# Service End POints
	public static function serviceEndPoints($poApp){
		return self::pr__get_app_location($poApp, "APP_SERVICE_ENDPOINT_LIST");
	}
	
	public static function serviceEndPoint($poTier, $piServiceID){
		$sURL = self::pr__get_tier_location($poTier, "APP_SERVICE_ENDPOINT_DASHBOARD");
		return $sURL."&serviceEndpoint=$piServiceID";
	}
	//###############################################################################################
	//# Transactions
	public static function businessTransactions($poApp){
		return  self::pr__get_app_location($poApp, "APP_BT_LIST");
	}
	
	public static function transaction($poApp, $piTransID){
		$sURL = self::pr__get_app_location($poApp, "APP_BT_DETAIL");
		return $sURL."&businessTransaction=$piTransID&dashboardMode=force";
	}

	//###############################################################################################
	//# snapshots
	
	public static function snapshot($poApp, $piTransID, $psGuid, $poTimes){
		$sURL = self::pr__get_app_location($poApp, "");
		$sTimeRange = self::time_command($poTimes);
		$sTimeRSD = self::time_command($poTimes, "rsdTime");
		return $sURL."&$sTimeRange&bypassAssociatedLocationsCheck=true&tab=1&businessTransaction=$piTransID&requestGUID=$psGuid&$sTimeRSD&dashboardMode=force";
	}
	
	public static function transaction_snapshots($poApp, $piTransID, $poTimes){
		$sURL = self::pr__get_app_location($poApp, "APP_BT_ALL_SNAPSHOT_LIST");
		$sTime = self::time_command($poTimes);
		return $sURL."&bypassAssociatedLocationsCheck=true&tab=1&businessTransaction=$piTransID&$sTime";
	}
	
	//###############################################################################################
	public static function webrum_pages($poApp){
		return self::pr__get_app_location($poApp, "EUM_PAGES_LIST");
	}
	public static function webrum($poApp){
		return self::pr__get_app_location($poApp, "APP_EUM_WEB_MAIN_DASHBOARD");
	}
	public static function webrum_detail($poApp, $psID){
		$sURL = self::pr__get_app_location($poApp, "EUM_PAGE_DASHBOARD");
		return $sURL."&addId=$psID";
	}
	public static function webrum_synthetics($poApp, $poTimes){
		$sUrl = self::pr__get_app_location($poApp, "EUM_SYNTHETIC_SCHEDULE_LIST");
		$sTime = self::time_command($poTimes);
		return "$sUrl&$sTime";
	}
			
}
?>
