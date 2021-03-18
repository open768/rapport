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
require_once("$appdlib/appdynamics.php");

//#################################################################
//# 
//#################################################################
class cAppDTier{
   public static $null_app = null;
   public static $db_app = null;
   public $name, $id, $app;
   function __construct($poApp, $psTierName, $psTierId) {	
		$this->app = $poApp;
		$this->name = $psTierName; 
		$this->id = $psTierId;
   }
   
	//*****************************************************************
	//*****************************************************************
	public function GET_transaction_names(){
		//find out the transactions in this tier - through metric heirarchy (but doesnt give the trans IDs)
		cDebug::enter();
		$aResults = []; 
		
		try{
			$metricPath = cAppDynMetric::tierTransactions($this->name);
			$aTierTransactions = cAppdynCore::GET_Metric_heirarchy($this->app->name, $metricPath, false);	
			if (!$aTierTransactions) return null;
			
			//so get the transaction IDs
			$aAppTrans= cAppdynUtil::get_trans_assoc_array($this->app);
			
			// and combine the two

			foreach ($aTierTransactions as $oTierTrans){
				if (!isset($aAppTrans[$oTierTrans->name])) continue;
				
				$sTransID = $aAppTrans[$oTierTrans->name];
				$oDetail = new cAppDDetails($oTierTrans->name, $sTransID, null, null);
				$aResults[] = $oDetail;
			}
			
			uasort($aResults, 'ad_sort_by_name');
		}
		catch (Exception $e){
			$aResults = null;
		}
		cDebug::leave();
		return $aResults;
	}
	
	
	//*****************************************************************
	public  function GET_ext_details($poTimes){
		global $aResults;
		$sApp = $this->app->name;
		$sTier = $this->name;
		
		cDebug::write("<h3>getting details for $sTier</h3>");
		//first get the metric heirarchy
		cAppdynUtil::flushprint(".");
		$oHeirarchy = $this->GET_ext_calls();
			
		//get the transaction IDs TBD
		$trid=1;
		
		//for each row in the browser get external calls per minute
		$aResults = array();
		foreach ($oHeirarchy as $row){
			cAppdynUtil::flushprint(".");
			$sOtherTier=$row->name;
			
			cDebug::write("<h4>other tier is $sOtherTier</h4>");
			cDebug::write("<b>Calls per min</b>");
			$oCalls = null;
			$oData = $this->GET_ExtCallsPerMin( $sOtherTier, $poTimes, "true");
			if ($oData)	$oCalls = cAppdynUtil::Analyse_Metrics( $oData);
				
			cDebug::write("<b>response times</b>");
			$oTimes = null;
			$oData = $this->GET_ExtResponseTimes($sOtherTier, $poTimes, "true");
			if ($oData)	
				$oTimes = cAppdynUtil::Analyse_Metrics( $oData);
			
			cDebug::write("<b>done</b>");
			
			$oDetails = new cAppDDetails($sOtherTier, $trid, $oCalls,  $oTimes);

			array_push($aResults, $oDetails);
		}
		
		//TODO
		return $aResults;
	}
	
	//*****************************************************************
   	public function GET_ext_calls(){
		$sTier = $this->name;
		cDebug::enter();
			$metricPath = "Overall Application Performance|$sTier|External Calls";
			$aData = cAppdynCore::GET_Metric_heirarchy($this->app->name, $metricPath, false);
			uasort ($aData, "ad_sort_by_name");
		cDebug::leave();
		return $aData;
	}

	//*****************************************************************
	public  function GET_ExtCallsPerMin($psTier2, $poTimes, $psRollup){
		$sMetricpath= cAppDynMetric::tierExtCallsPerMin($this->name, $psTier2);
		return cAppdynCore::GET_MetricData($this->app, $sMetricpath, $poTimes, $psRollup);
	}	

	//*****************************************************************
	public function GET_ExtResponseTimes($psTier2, $poTimes, $psRollup){
		$sMetricpath= cAppDynMetric::tierExtResponseTimes($this->name, $psTier2);
		return cAppdynCore::GET_MetricData($this->app, $sMetricpath, $poTimes, $psRollup);
	}
	//*****************************************************************
	public  function GET_ServiceEndPoints(){
		if ( cAppdyn::is_demo()) return cAppDynDemo::GET_TierServiceEndPoints(null,null);
		$sMetricpath= cAppDynMetric::tierServiceEndPoints($this->name);
		$oData = cAppdynCore::GET_Metric_heirarchy($this->app->name, $sMetricpath, false);
		return $oData;
	}
	
	//*****************************************************************
	public function GET_Nodes(){
		cDebug::enter();
		$sMetricpath=cAppDynMetric::InfrastructureNodes($this->name);
		$aData = cAppdynCore::GET_Metric_heirarchy($this->app->name, $sMetricpath, false);
		uasort($aData, 'ad_sort_by_name');
		cDebug::leave();
		return  $aData;
	}

	public function GET_JDBC_Pools($psNode=null){
		cDebug::enter();
		$sMetricpath=cAppDynMetric::InfrastructureJDBCPools($this->name, $psNode);
		$oData = cAppdynCore::GET_Metric_heirarchy($this->app->name, $sMetricpath, false);
		cDebug::leave();
		return  $oData;
	}
	
	public function GET_DiskMetrics(){
		cDebug::enter();
		$sMetricpath=cAppDynMetric::InfrastructureNodeDisks($this->name, null);
		$aData = cAppdynCore::GET_Metric_heirarchy($this->app->name, $sMetricpath, true);
		
		$aOut = [];
		foreach ($aData as $oEntry)
			if ($oEntry->type === "leaf") $aOut[] = $oEntry;
		
		uasort($aOut, 'ad_sort_by_name');
		cDebug::leave();
		return  $aOut;
	}
	
	public function GET_NodeDisks($psNode){
		cDebug::enter();
		$sMetricpath=cAppDynMetric::InfrastructureNodeDisks($this->name, $psNode);
		$aData = cAppdynCore::GET_Metric_heirarchy($this->app->name, $sMetricpath, true);
		
		$aOut = [];
		foreach ($aData as $oEntry)
			if ($oEntry->type === "folder") $aOut[] = $oEntry;
		
		uasort($aOut, 'ad_sort_by_name');
		cDebug::leave();
		return  $aOut;
	}
}
?>
