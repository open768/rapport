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
require_once("$phpinc/ckinc/array.php");
require_once("$appdlib/common.php");
require_once("$appdlib/core.php");
require_once("$appdlib/account.php");


//#################################################################
//# 
//#################################################################
function AD_sort_fn($a,$b)
{
    $v1 = $a->startTimeInMillis;
    $v2 = $b->startTimeInMillis;
    if ($v1==$v2) return 0;
    return ($v1 < $v2) ? -1 : 1;
}


function ad_sort_by_name($po1, $po2){
	return strcasecmp ($po1->name, $po2->name);
}

function sort_machine_agents( $po1, $po2){
	return strcasecmp ($po1->applicationIds[0].".".$po1->hostName, $po2->applicationIds[0].".".$po2->hostName);	
}
function sort_appserver_agents( $po1, $po2){
	return strcasecmp (
		"$po1->applicationName.$po1->applicationComponentName.$po1->hostName", 
		"$po2->applicationName.$po2->applicationComponentName.$po2->hostName"
	);	
}

function ad_sort_downloads($po1, $po2){
	return strcasecmp ($po1->title, $po2->title);	
}

//#################################################################
//# 
//#################################################################
class cCallsAnalysis{
    public $max, $min, $avg, $sum, $count, $extCalls;
}
class cExtCallsAnalysis{
    public $count=0, $totalTime=0, $exitPointName, $toComponentID;
}

//#################################################################
//# 
//#################################################################
class cAppDynTransFlow{
	public $name = null;
	public $children = [];
	
	//*****************************************************************
	public function walk($psApp, $psTier, $psTrans){
		cDebug::enter();
		
		$sMetricPath = cAppDynMetric::transExtNames($psTier, $psTrans);
		$this->walk_metric($psApp, $sMetricPath);
		$this->name = $psTrans;
		
		cDebug::leave();
	}

	//*****************************************************************
	protected function walk_metric($psApp, $psMetricPath){
		cDebug::enter();

		$aCalls = cAppdynCore::GET_Metric_heirarchy($psApp, $psMetricPath, false);
		cDebug::write($psMetricPath);
		
		foreach ($aCalls as $oCall)
			if ($oCall->type == "folder") {
				$sMetricPath = $psMetricPath . "|".$oCall->name."|".cAppDynMetric::EXT_CALLS;
				
				$oChild = new cAppDynTransFlow();
				$this->children[] = $oChild;
				$oChild->name = $oCall->name;
				$oChild->walk_metric($psApp, $sMetricPath);
				
			}
			
		cDebug::leave();
	}
	
	//*****************************************************************
	private function pr_add_children($psApp, $psMetric, $paCalls){
	}
}

//#################################################################
//# CLASSES
//#################################################################

class cAppdynUtil {
	private static $maAppnodes = null;
	public static $SHOW_PROGRESS = true;
	
	//*****************************************************************
	public static function get_trans_assoc_array($poApp)
	{	
		$aData = [];
		$aTrans = $poApp->GET_Transactions();
		foreach ($aTrans as $oTrans)
			$aData[$oTrans->name] = $oTrans->id;
			
		return $aData;
	}
	//*****************************************************************
	public static function MergeMetricNodes($paData){
		if (count($paData) == 0)
			return null;
		elseif (count($paData) == 1)
			return array_pop($paData);
		else{
			$aNew = [];
			while (count($paData) >0)
			{
				$aPopped = array_pop($paData);
				if (count($aPopped) > 0){
					while (count($aPopped) > 0)
					{
						$aRow = array_pop($aPopped);
						$aNew[] = $aRow;
					}
				}
			}
			return $aNew;
		}
	}


	//*****************************************************************
	public static function Analyse_Metrics($poData)
	{
		$max = 0; 
		$count = 0;
		$items = 0;
		$min = -1;
		$sum=0;
		$avg=0;
		
		foreach( $poData as $oRow)
		{
			$value = $oRow->value;	
		
			$max = max($max, $value, $oRow->max);
			if ($value >0){
				if ($min==-1)
					$min=$value;
				else
					$min = min($min, $value);
			}
				
			if ($value>0){
				$count+=$oRow->count;
				$sum+=$value;
				$items++;
			}
		}
		
		if ($min==-1) $min = 0;
		if ($count>0)
			$avg = $sum/$items;
		
		$oResult = new cCallsAnalysis();
		$oResult->max = $max;
		$oResult->min = $min;
		$oResult->sum = $sum;
		$oResult->avg = round($avg,2);
		$oResult->count= $count;
		
		return $oResult;
	}
	
	//*****************************************************************
	public static function Analyse_heatmap($poData){
		$aDays = [];
		$aHours = [];

		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
		function pr__add_to_array(&$paArray, $psCol, $psRow, $psValue){
			if (!isset($paArray[$psCol])) $paArray[$psCol]=[];
			if (!isset($paArray[$psCol][$psRow])) $paArray[$psCol][$psRow]=0;
			$paArray[$psCol][$psRow] += $psValue;
		};
		
		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
		function pr__normalise_array(&$paArray){
			$iMax=0;
			foreach ($paArray as $sCol=>$aRows)
				foreach ($aRows as $sRow =>$iValue)
					if ($iValue > $iMax) $iMax = $iValue;
				
			foreach ($paArray as $sCol=>$aRows)
				foreach ($aRows as $sRow =>$iValue)
					$paArray[$sCol][$sRow] = $iValue/$iMax;
		}
		
		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
		foreach( $poData as $oRow){
			$milli = $oRow->startTimeInMillis;
			$hour = date("H", $milli/1000);
			$min = date("i", $milli/1000); 
			$day = date("w", $milli/1000); 
			$value = $oRow->value;

			pr__add_to_array($aDays,$day,$hour,$value);
			pr__add_to_array($aHours,$hour,$min, $value);
		}
		
		pr__normalise_array($aDays);
		pr__normalise_array($aHours);
		
		return ["days"=>$aDays, "hours"=>$aHours];
	}
	
	//*****************************************************************
	public static function extract_bt_name($psMetric, $psTier){
		$sLeft = cAppdynMetric::tierTransactions($psTier);
		$sOut = substr($psMetric, strlen($sLeft)+1);
		$iPos = strpos($sOut, cAppdynMetric::RESPONSE_TIME);
		$sOut = substr($sOut, 0, $iPos -1);
		return $sOut;
	}
	
	//*****************************************************************
	public static function extract_error_name($psTier, $psMetric){
		$sTier = preg_quote($psTier);
		$sPattern = "/\|$sTier\|(.*)\|Errors per Minute/";
		if (preg_match($sPattern, $psMetric, $aMatches))
			return $aMatches[1];
		else
			cDebug::error("no match $psMetric with $sPattern");
	}
	
	//*****************************************************************
	public static function extract_RUM_name($psType, $psMetric){
		$sType = preg_quote($psType);
		$sPattern = "/\|$sType\|([^\|]+)\|/";
		if (preg_match($sPattern, $psMetric, $aMatches))
			return $aMatches[1];
		else
			cDebug::error("no match $psMetric with $sPattern");
	}
	
	//*****************************************************************
	public static function extract_RUM_id($psType, $psMetricName){
		$sType="Base Page";
		if ($psType == cAppdynMetric::AJAX_REQ) $sType="AJAX Request";
		$sPattern = "/\|$sType:(\d+)\|/";
		if (preg_match($sPattern, $psMetricName, $aMatches))
			return $aMatches[1];
		else
			cDebug::error("no match '$psMetricName' with '$sPattern'");
	}
	
	//*****************************************************************
	public static function extract_bt_id($psMetricName){
		if (preg_match("/\|BT:(\d+)\|/", $psMetricName, $aMatches))
			return $aMatches[1];
		else
			cDebug::error("no match");
	}

	public static function make_time_obj($piTimeinMs){
		$oTime = new cAppDynTimes;
		$oTime->start = $piTimeinMs-5000;
		$oTime->end = $piTimeinMs+5000;
		return $oTime;
	}
	//*****************************************************************
	public static function controller_time_command($poTime, $psKey="time-range-type"){
		return "$psKey=BETWEEN_TIMES&start-time=".$poTime->start."&end-time=".$poTime->end;
	}
	//*****************************************************************
	public static function controller_short_time_command($poTime,$psKey="timeRange"){
		$sTime = "Custom_Time_Range.BETWEEN_TIMES.".$poTime->end.".".$poTime->start.".60";
		if ($psKey)
			return "$psKey=$sTime";
		else
			return $sTime;
	}
	
	//*****************************************************************
	public static function timestamp_to_date( $piMs){
		$iEpoch = (int) ($piMs/1000);
		return date(cCommon::ENGLISH_DATE_FORMAT, $iEpoch);
	}
	
	//*****************************************************************
	public static function extract_agent_version($psInput){
		if (preg_match("/^[\d\.]+$/",$psInput))
			return $psInput;
		
		if (preg_match("/\s+(v[\d\.]+\s\w+)/",$psInput, $aMatches))
			return $aMatches[1];
		else	
			return "unknown $psInput";
	}
	
	//*****************************************************************
	public static function get_node_id($poApp, $psNodeName){
		$aMachines = $poApp->GET_Nodes();
		$sNodeID = null;
		
		foreach ($aMachines as $aNodes){
			foreach ($aNodes as $oNode)
				if ($oNode->name == $psNodeName){
					$sNodeID = $oNode->id;
					cDebug::write ("found $sNodeID");
					break;
				}
			if ($sNodeID) break;
		}
		
		return $sNodeID;
	}
	
	//*****************************************************************
	public static function get_node_name($poApp, $psNodeID){
		$aMachines = $poApp->GET_Nodes();
		$sNodeName = null;
		
		foreach ($aMachines as $aNodes){
			foreach ($aNodes as $oNode)
				if ($oNode->id == $psNodeID){
					$sNodeName = $oNode->name;
					cDebug::write ("found $sNodeName");
					break;
				}
			if ($sNodeName) break;
		}
		
		return $sNodeName;
	}
	
	//*****************************************************************
	public static function get_matching_extcall($poApp, $psExt){
		$aTiers = $poApp->GET_Tiers();
		foreach ($aTiers as $oTier){
			$aTierExt = $oTier->GET_ext_calls();
			foreach ($aTierExt as $oExt)
				if ( strpos($oExt->name, $psExt) !== false )
					return $oExt->name;
		}
		return null;
	}
	
	//*****************************************************************
	public static function ignore_exitCall($poExitCall){
		if ($poExitCall->detailString === "Get Pooled Connection From Datasource") return true;
		return false;
	}
	
	//*****************************************************************
	public static function count_flow_ext_calls($poFlow){
		cDebug::enter();
		$oExtCalls = new cAssocArray;
		$aNodes = $poFlow->nodes;
		
		foreach ($aNodes as $oNode){
			$aSegments = $oNode->requestSegmentDataItems;
			if (count($aSegments)==0) continue;
			
			foreach ($aSegments as $oSegment){
				$aExitCalls = $oSegment->exitCalls;
				if (count($aExitCalls)==0) continue;
				
				foreach ($aExitCalls as $oExitCall){
					$sExtName = $oExitCall->exitPointName.":".$oExitCall->toComponentId;
					if (self::ignore_exitCall($oExitCall)) continue;
					
					
					$iCount = 0;
					if ($oExtCalls->key_exists($sExtName)) 
						$oCounter = $oExtCalls->get($sExtName);
					else{
						$oCounter = new cExtCallsAnalysis;
						$oCounter->count = 0;
						$oCounter->exitPointName = $oExitCall->exitPointName;
						$oCounter->toComponentID = $oExitCall->toComponentId;
						$oExtCalls->set($sExtName, $oCounter);
					}
					
					$oCounter->count += $oExitCall->count;
					$oCounter->totalTime += $oExitCall->timeTakenInMillis;
				}
			}
		}
		
		cDebug::leave();		
		return $oExtCalls;
	}
	
	//*****************************************************************
	public static function count_snapshot_ext_calls($poShapshot){
		cDebug::enter();
		
		//---------------- get the flow
		try{
			$oFlow = cAppDynRestUI::GET_snapshot_flow($poShapshot);
		}catch (Exception $e){
			return null;
		}
		
		//---------------- analyse the flow
		$oExtCalls = self::count_flow_ext_calls($oFlow);
		cDebug::leave();		
		return $oExtCalls;
	}

	public static function flushprint($psChar = cCommon::PROGRESS_CHAR){
		if (self::$SHOW_PROGRESS) cCommon::flushprint($psChar);
	}

	
}

?>
