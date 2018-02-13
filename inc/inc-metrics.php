<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("$phpinc/appdynamics/metrics.php");
require_once("$phpinc/appdynamics/account.php");
require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/hash.php");



//######################################################################
class cMergedMetrics{
	
	public $sourceData = [];
	public $data = [];
	public $dates = [];
	
	public function add( $poMetricOutput){
		if (count($poMetricOutput->data) >0){
			$this->sourceData[] = $poMetricOutput;
			$aAssocList = [];
			foreach ($poMetricOutput->data as $oItem){
				$this->dates[$oItem->date]=1;
				$aAssocList[$oItem->date] = $oItem;
			}
			$this->data[] = $aAssocList;
		}
	}
	//**********************************************************************
	private function pr__get_filename(){
		$sAggregated = "";
		
		foreach ($this->sourceData as $oMetricOutput)
			$sAggregated .= $oMetricOutput->app.$oMetricOutput->metric;
		$sHash = cHash::hash($sAggregated);
		cDebug::write($sHash);
		return $sHash;
	}
	
	//**********************************************************************
	public function write_csv(){
		//--write CSV header to file
		cCommon::echo("Merged_metrics:, ".date(cCommon::EXCEL_DATE_FORMAT,time()));
		cCommon::echo("");
		
		$sAppLine = "application";
		$sMetricLine = "metric";
		$sColumnLine = "";
		foreach ($this->sourceData as $oMetricOutput){
			$sAppLine .= ",$oMetricOutput->app,";
			$sMetricLine .= ",$oMetricOutput->metric,";
			$sColumnLine .= ",value,max";
		}
		cCommon::echo($sAppLine);
		cCommon::echo($sMetricLine);
		cCommon::echo($sColumnLine);
		
		//--sort the dates
		$aKeys = array_keys($this->dates);
		uasort($aKeys, "pr__sort_dates");
		
		//--output data 
		foreach($aKeys as $sDate){
			$oDate = DateTime::createFromFormat(DateTime::W3C, $sDate);
			$sXLDate = $oDate->format(cCommon::EXCEL_DATE_FORMAT);

			$sLine = $sXLDate;
			foreach ($this->data as $aMetrics){
				if (array_key_exists($sDate,$aMetrics)){
					$oItem = $aMetrics[$sDate];
					$sLine.=",$oItem->value,$oItem->max";
				}else
					$sLine.=",,";
			}
			cCommon::echo($sLine);
		}
	}
}

function pr__sort_dates($a,$b){
	return strtotime($a) - strtotime($b);
}

//######################################################################
class cMetric{
	public static function get_metric($psApp, $psMetric, $pbPreviousPeriod = false){
		$oOutput = new cMetricOutput;
		$oOutput->metric = $psMetric;
		$oOutput->app = $psApp;
		$aData = null;
		
		if (strstr($psMetric, cAppDynMetric::USAGE_METRIC)){
			//license usage metrics are special
			$aParams = explode("/",$psMetric);
			$sModule = $aParams[1];
			$iDuration = $aParams[2];
			
			$oOutput->epoch_start = (time() - ($iDuration*cCommon::SECONDS_IN_MONTH))*1000;
			$oOutput->epoch_end = time()*1000;

			try{
				$aData = cAppDynAccount::GET_license_usage($sModule, $iDuration);
			}
			catch (Exception $e){}
			
			if ($aData)
				foreach ($aData as $oItem){
					$oDate = date_create_from_format(cAppdynCore::DATE_FORMAT,$oItem->date);
					$sDate = $oDate->format(DateTime::W3C);
					$oOutput->add($sDate,$oItem->value);
				}
		}else{
			//normal metrics
			$oTime= cRender::get_times();
			$epochTo = $oTime->end;
			$epochFrom = $oTime->start;
			$oOutput->epoch_start = $epochFrom;
			$oOutput->epoch_end = $epochTo;
			
			if ($pbPreviousPeriod){
				$iDiff = $epochTo - $epochFrom;
				$epochTo = $epochFrom;
				$epochFrom = $epochTo - $iDiff;
				
				$oTime->end = $epochTo;
				$oTime->start = $epochFrom;
			}
			
			try{
				if (cAppDyn::is_demo()){
					$aData = cAppDynDemo::GET_MetricData($psApp, $psMetric, $oTime, false);
				}else{
					$aData = cAppDynCore::GET_MetricData($psApp, $psMetric, $oTime, false);
				}
			}
			catch (Exception $e){}
			if ($aData){
				//add the other data
				foreach ($aData as $oRow){
					$sDate = date(DateTime::W3C, $oRow->startTimeInMillis/1000); 
					$iMaxval = max($oRow->max, $oRow->value);
					
					$oOutput->add($sDate,$oRow->value,$iMaxval );
				}
			}
		}
		
		return $oOutput;
	}
}