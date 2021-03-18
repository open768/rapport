<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//see 
require_once("$appdlib/appdynamics.php");

//#################################################################
//# 
//#################################################################
class cAppDApp{
	public static $null_app = null;
	public static $db_app = null;
	public $name, $id;
	function __construct($psAppName, $psAppId) {	
		$this->name = $psAppName;
		$this->id = $psAppId;
	}
   
	//*****************************************************************
	public function GET_Tiers(){
		if ( cAppDyn::is_demo()) return cAppDynDemo::GET_Tiers($this);
		$sApp = rawurlencode($this->name);
		$aData = cAppdynCore::GET("$sApp/tiers?" );
		if ($aData) uasort($aData,"ad_sort_by_name");
		
		$aOutTiers = [];

		//convert to tier objects and populate the app
		foreach ($aData as $oInTier){
			$oOutTier = new cAppDTier($this, $oInTier->name, $oInTier->id);
			$aOutTiers[] = $oOutTier;
		}
		
		return $aOutTiers;
	}
	
	//*****************************************************************
	public function GET_ExtTiers(){
		if ( cAppDyn::is_demo()) return cAppDynDemo::GET_AppExtTiers(null);
		cDebug::enter();
		$sMetricPath= cAppDynMetric::appBackends();
		$aMetrics = cAppdynCore::GET_Metric_heirarchy($this->name, $sMetricPath,false); //dont cache
		if ($aMetrics) uasort($aMetrics,"ad_sort_by_name");
		cDebug::leave();
		return $aMetrics;
	}

	//*****************************************************************
	public function GET_InfoPoints($poTimes){
		if ( cAppDyn::is_demo()) return cAppDynDemo::GET_AppInfoPoints(null);
		return cAppdynCore::GET_Metric_heirarchy($this->name,cAppDynMetric::INFORMATION_POINTS, false, $poTimes);
	}

	//*****************************************************************
	//see events reference at https://docs.appdynamics.com/display/PRO14S/Events+Reference
	public function GET_Events($poTimes, $psEventType = null){
		$sApp = rawurlencode($this->name);
		$sTimeQs = cAppdynUtil::controller_time_command($poTimes);
		if ($psEventType== null) $psEventType = cAppDyn::ALL_EVENT_TYPES;
		$sSeverities = cAppDyn::ALL_SEVERITIES;
		
		$sEventsUrl = cHttp::build_url("$sApp/events", "severities", $sSeverities);
		$sEventsUrl = cHttp::build_url($sEventsUrl, "Output", "JSON");
		$sEventsUrl = cHttp::build_url($sEventsUrl, "event-types", $psEventType);
		$sEventsUrl = cHttp::build_url($sEventsUrl, $sTimeQs);
		return cAppDynCore::GET($sEventsUrl );
	}

	//*****************************************************************
	public function GET_Nodes(){
		$sID = $this->id;
		
		$aResponse = cAppDynCore::GET("$sID/nodes?",true);

		$aOutput = [];
		foreach ($aResponse as $oNode){
			$iMachineID = $oNode->machineId;
			if (!isset($aOutput[(string)$iMachineID])) $aOutput[(string)$iMachineID] = [];
			$aOutput[(string)$iMachineID][] = $oNode;
		}
		ksort($aOutput );
		
		return $aOutput;
	}

	//*****************************************************************
	public function GET_Transactions(){		
		$sApp = rawurlencode($this->name);
		return cAppDynCore::GET("$sApp/business-transactions?" );
	}

	//*****************************************************************
	public function GET_Backends(){
		if ( cAppDyn::is_demo()) return cAppDynDemo::GET_Backends(null);
		$sMetricpath= cAppDynMetric::backends();
		return cAppdynCore::GET_Metric_heirarchy($this->name, $sMetricpath, false); //dont cache
	}

	//*****************************************************************
	public function GET_snaphot_info($psTransID, $poTimes){
		/*should use instead
		eg https://xxx.saas.appdynamics.com/controller/restui/snapshot/snapshotListDataWithFilterHandle		{"firstInChain":false,"maxRows":600,"applicationIds":[1424],"businessTransactionIds":[],"applicationComponentIds":[4561],"applicationComponentNodeIds":[],"errorIDs":[],"errorOccured":null,"userExperience":[],"executionTimeInMilis":null,"endToEndLatency":null,"url":null,"sessionId":null,"userPrincipalId":null,"dataCollectorFilter":null,"archived":null,"guids":[],"diagnosticSnapshot":null,"badRequest":null,"deepDivePolicy":[],"rangeSpecifier":{"type":"BEFORE_NOW","durationInMinutes":15}}		
		*/
		
		$sApp = rawurlencode($this->name);
		$sUrl = cHttp::build_url("$sApp/request-snapshots", cAppdynUtil::controller_time_command($poTimes));
		$sUrl = cHttp::build_url($sUrl, "application_name", $sApp);
		//$sUrl = cHttp::build_url($sUrl, "application-component-ids", $psTierID);
		$sUrl = cHttp::build_url($sUrl, "business-transaction-ids", $psTransID);
		$sUrl = cHttp::build_url($sUrl, "output", "JSON");
		return cAppDynCore::GET($sUrl);
	}
}

?>
