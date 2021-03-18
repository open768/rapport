<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
require_once("$appdlib/core.php");

class cAppdynRestUISynthList{
	public $applicationId= -1;
	public $timeRangeString ="";	
}

class cAppdynRestUITime{
	public $type="BETWEEN_TIMES";
	public $durationInMinutes = 60;
	public $endTime = -1;
	public $startTime = -1;
	public $timeRange=null;
	public $timeRangeAdjusted=false;
}


class cAppdynRestUISnapshotFilter{
	public $applicationIds = [];
	public $applicationComponentIds = [];
	public $sepIds = [];
	public $rangeSpecifier = null;
	
	function __construct() {
		$this->rangeSpecifier = new cAppdynRestUITime;
	}
}
class cAppdynRestUIRequest{
	public $applicationIds = [];
	public $guids = [];
	public $rangeSpecifier = null;
	public $needExitCalls = true;
	
	function __construct(){
		$this->rangeSpecifier = new cAppdynRestUITime;
	}
}

class cAppdSynthResponse{
	public $id;
	public $name;
	public $app;
	public $rate;
	public $executions;
	public $durations;
	public $config;
	public $raw_data;
}


//#####################################################################################
//#
//#####################################################################################
class cAppDynRestUI{
	public static $oTimes = null;
	
	public static function GET_database_agents(){
		$sURL = "agent/setting/getDBAgents";
		return  cAppdynCore::GET_restUI($sURL);
	}
	public static function GET_machine_agents(){
		$aAgents = cAppdynCore::GET_restUI("agent/setting/allMachineAgents");
		uasort($aAgents,"sort_machine_agents");
		return  $aAgents;
	}
	public static function GET_appserver_agents(){
		$aAgents = cAppdynCore::GET_restUI("agent/setting/getAppServerAgents");
		uasort($aAgents,"sort_appserver_agents");
		return  $aAgents;
	}

	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//* Nodes  
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	public static function GET_Node_details($piAppID, $piNodeID){
		$sURL = "dashboardNodeViewData/$piAppID/$piNodeID";
		return  cAppdynCore::GET_restUI($sURL);
	}
	
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//* Snapshots (warning this uses an undocumented API)
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	public static function GET_snapshot_segments($psGUID, $piSnapTime){
		cDebug::enter();
			$oTime = cAppdynUtil::make_time_obj($piSnapTime);
			$sTimeUrl = cAppdynUtil::controller_short_time_command( $oTime);
			$sURL = "snapshot/getRequestSegmentData?requestGUID=$psGUID&$sTimeUrl";
			$aResult = cAppdynCore::GET_restUI($sURL);
		cDebug::leave();
		return  $aResult;
	}
	
	//************************************************************************************
	public static function GET_snapshot_problems($poApp,$psGUID, $piSnapTime){
		cDebug::enter();
			$oTime = cAppdynUtil::make_time_obj($piSnapTime);
			$sTimeUrl = cAppdynUtil::controller_short_time_command( $oTime, "time-range");
			$sURL = "snapshot/potentialProblems?request-guid=$psGUID&applicationId=$poApp->id&$sTimeUrl&max-problems=50&max-rsds=30&exe-time-threshold=5";
			$aResult = cAppdynCore::GET_restUI($sURL);
		cDebug::leave();
		return  $aResult;
	}
	
	//************************************************************************************
	public static function GET_snapshot_flow($poSnapShot){
		cDebug::enter();
			$oTime = cAppdynUtil::make_time_obj($poSnapShot->serverStartTime);
			$sAid = $poSnapShot->applicationId;
			$sBtID = $poSnapShot->businessTransactionId;
			$sGUID = $poSnapShot->requestGUID;
			$sTimeUrl = cAppdynUtil::controller_short_time_command( $oTime);
			$sURL = "snapshotFlowmap/distributedSnapshotFlow?applicationId=$sAid&businessTransactionId=$sBtID&requestGUID=$sGUID&eventType=&$sTimeUrl&mapId=-1";
			$oResult = cAppdynCore::GET_restUI($sURL);
		cDebug::leave();
		
		return $oResult;
	}

	//************************************************************************************
	public static function GET_snapshot_expensive_methods($psGUID, $piSnapTime){
		cDebug::enter();
			$oTime = cAppdynUtil::make_time_obj($piSnapTime);
			$sTimeUrl = cAppdynUtil::controller_short_time_command( $oTime);
			$sURL = "snapshot/getMostExpensiveMethods?limit=30&max-rsds=30&$sTimeUrl&mapId=-1";
			$oResult = cAppdynCore::GET_restUI_with_payload($sURL,$psGUID);
		cDebug::leave();
		
		return $oResult;
	}
	
	//************************************************************************************
	public static function GET_service_end_points($poTier){
		cDebug::enter();
		//serviceEndpoint/list2/1424/1424/APPLICATION?time-range=last_1_hour.BEFORE_NOW.-1.-1.60
		$iTid = $poTier->id;
		$iAid = $poTier->app->id;
		$sURL = "serviceEndpoint/list2/$iAid/$iAid/APPLICATION?time-range=last_1_hour.BEFORE_NOW.-1.-1.60";
		$oResult = cAppdynCore::GET_restUI($sURL);
		
		//now filter the results for the tier id
		$aEndPoints = [];
		foreach( $oResult->serviceEndpointListEntries as $oService){
			if ($oService->applicationComponentId == $iTid){
				$oItem = new cAppDDetails($oService->name, $oService->id, null,null);
				$oItem->type = $oService->type;
				$aEndPoints[] = $oItem;
			} 
		}
		uasort($aEndPoints,"ad_sort_by_name");
		cDebug::leave();
		return $aEndPoints;
	}
	
	//************************************************************************************
	public static function GET_Service_end_point_snapshots($poTier, $piServiceEndPointID, $oTime){
		cDebug::enter();
		//{"applicationIds":[1424],"applicationComponentIds":[4561],"sepIds":[6553581],"rangeSpecifier":{"type":"BEFORE_NOW","durationInMinutes":60},"maxRows":600}
		
		$sURL = "snapshot/snapshotListDataWithFilterHandle";
		$oFilter = new cAppdynRestUISnapshotFilter;
		$oFilter->applicationIds[] = intval($poTier->app->id);
		$oFilter->applicationComponentIds[] = intval($poTier->id);
		$oFilter->sepIds[] = intval($piServiceEndPointID);
		$oFilter->maxRows = 600;
		$oFilter->rangeSpecifier->startTime = $oTime->start;
		$oFilter->rangeSpecifier->endTime = $oTime->end;

		$sPayload = json_encode($oFilter);
		cDebug::extra_debug($sPayload);
		$oResult = cAppdynCore::GET_restUI_with_payload($sURL,$sPayload);
		
		cDebug::leave();
		return $oResult;
	}
	
	//************************************************************************************
	public static function GET_Synthetic_jobs($poApp, $oTime, $pbDetails){
		cDebug::enter();
		$oRequest = new cAppdynRestUISynthList;
		$oRequest->applicationId = (int)$poApp->id;
		$oRequest->timeRangeString = cAppdynUtil::controller_short_time_command( $oTime,null);
		$sURL = "synthetic/schedule/getJobList";
		$sPayload = json_encode($oRequest);
		
		try{
			$oResult = cAppdynCore::GET_restUI_with_payload($sURL,$sPayload,true);
		}catch (Exception $e){
			$oResult = null;
		}
		
		$aSyth = [];
		foreach ($oResult->jobListDatas as $oJob){
			$oSummary = new cAppdSynthResponse;
			$oSummary->app = $poApp;
			$oSummary->id = $oJob->config->id;
			$oSummary->rate = $oJob->config->rate;
			$oSummary->name = $oJob->config->description;
			
			if ($pbDetails){
				if ($oJob->metrics->sessionDuration->count >0)
					$oSummary->durations = $oJob->metrics->sessionDuration;	
				if ($oJob->metrics->jobExecutions->count >0)
					$oSummary->executions = $oJob->metrics->jobExecutions;	
				if (cDebug::is_debugging() )
					$oSummary->raw_data = $oJob;	
				$oSummary->config = $oJob->config->performanceCriteria;
			}
			$aSyth[] = $oSummary;
		}
		cDebug::leave();
		return $aSyth;		
	}
}
	
	
	


