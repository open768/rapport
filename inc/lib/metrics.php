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

//#################################################################
//# CLASSES
//#################################################################
class cMetricItem{
	public $value;
	public $max;
	public $date;
}

//######################################################################
class cMetricOutput{
	public $div;
	public $metric;
	public $app;
	public $data = [];
	public $epoch_start;
	public $epoch_end;
	
	public function add($psDate, $piValue, $piMax = null){
		$oItem = new cMetricItem;
		$oItem->value = $piValue;
		$oItem->max = $piMax;
		$oItem->date = $psDate;
		
		$this->data[] = $oItem;
	}
}

class cAppDynInfraMetricTypeDetails{
	public $type;
	public $metric;
}

function ad_sort_by_metric_short($po1, $po2){
	return strcasecmp ($po1->metric->short, $po2->metric->short);
}

//######################################################################
class cAppDynInfraMetric{
	const METRIC_TYPE_INFR_AVAIL = "mtia";
	const METRIC_TYPE_INFR_AGENT_METRICS = "mtiam";
	const METRIC_TYPE_INFR_AGENT_INVALID_METRICS = "mtiaim";
	const METRIC_TYPE_INFR_AGENT_LICENSE_ERRORS = "mtiale";
	const METRIC_TYPE_INFR_CPU_BUSY = "mticb";
	const METRIC_TYPE_INFR_MEM_FREE = "mtimf";
	const METRIC_TYPE_INFR_NETWORK_IN = "mtini";
	const METRIC_TYPE_INFR_NETWORK_OUT = "mtino";
	const METRIC_TYPE_INFR_JAVA_HEAP_USED = "mtijhu";
	const METRIC_TYPE_INFR_JAVA_HEAP_USEDPCT = "mtijup";
	const METRIC_TYPE_INFR_JAVA_GC_TIME = "mtijgt";
	const METRIC_TYPE_INFR_JAVA_CPU_USAGE = "mtijcpu";
	const METRIC_TYPE_INFR_DOTNET_HEAP_USED = "mtidhu";
	const METRIC_TYPE_INFR_DOTNET_GC_PCT = "mtidgp";
	const METRIC_TYPE_INFR_DOTNET_ANON_REQ = "mtidar";
	
	//**************************************************************************
	public static function getInfrastructureMetricDetails($poTier){
		$aTypes = self::getInfrastructureMetricTypes();
		$aOut = [];
		foreach ( $aTypes as $sType){
			$oMetric = cAppDynInfraMetric::getInfrastructureMetric($poTier->name,null,$sType);
			$oDetails = new cAppDynInfraMetricTypeDetails;
			$oDetails->type = $sType;
			$oDetails->metric = $oMetric;
			$aOut[] = $oDetails;
		}
		uasort($aOut,"ad_sort_by_metric_short");
		return $aOut;
	}
		
	public static function getInfrastructureMetricTypes(){
		$aMetricTypes = [cAppDynMetric::METRIC_TYPE_ACTIVITY, cAppDynMetric::METRIC_TYPE_RESPONSE_TIMES];
		$aMiscInfraMetricTypes = self::getInfrastructureMiscMetricTypes();
		$aAgentMetricTypes = self::getInfrastructureAgentMetricTypes();
		$aMemoryMetricTypes = self::getInfrastructureMemoryMetricTypes();
		$aMetricTypes = array_merge($aMetricTypes, $aMiscInfraMetricTypes,$aAgentMetricTypes, $aMemoryMetricTypes);
		return $aMetricTypes;
	}
	
	//**************************************************************************
	public static function getInfrastructureAgentMetricTypes(){
		$aTypes = 
		 [
			self::METRIC_TYPE_INFR_AVAIL,
			self::METRIC_TYPE_INFR_AGENT_METRICS,
			self::METRIC_TYPE_INFR_AGENT_INVALID_METRICS,
			self::METRIC_TYPE_INFR_AGENT_LICENSE_ERRORS,
		];
		
		//sort the list
		$aSortedList = [];
		foreach ($aTypes as $sMetricType){
			$oDetails = self::getInfrastructureMetric(null,null,$sMetricType);
			$aSortedList[$oDetails->caption] = $oDetails;
		}
		uksort($aSortedList, "strnatcasecmp");
		$aTypes = [];
		foreach ($aSortedList as $oDetails)
			$aTypes[] = $oDetails->type;
		
		return $aTypes;
	}

	//**************************************************************************
	public static function getInfrastructureMemoryMetricTypes(){
		$aTypes = 
		 [
			self::METRIC_TYPE_INFR_MEM_FREE,
			self::METRIC_TYPE_INFR_JAVA_HEAP_USEDPCT,
			self::METRIC_TYPE_INFR_JAVA_HEAP_USED,
			self::METRIC_TYPE_INFR_JAVA_GC_TIME,
			self::METRIC_TYPE_INFR_DOTNET_HEAP_USED,
			self::METRIC_TYPE_INFR_DOTNET_GC_PCT
		];
		
		//sort the list
		$aSortedList = [];
		foreach ($aTypes as $sMetricType){
			$oDetails = self::getInfrastructureMetric(null,null,$sMetricType);
			$aSortedList[$oDetails->caption] = $oDetails;
		}
		uksort($aSortedList, "strnatcasecmp");
		$aTypes = [];
		foreach ($aSortedList as $oDetails)
			$aTypes[] = $oDetails->type;
		
		return $aTypes;
	}
	
	//**************************************************************************
	public static function getInfrastructureMiscMetricTypes(){
		$aTypes = 
		 [
			self::METRIC_TYPE_INFR_CPU_BUSY,
			self::METRIC_TYPE_INFR_JAVA_CPU_USAGE,
			self::METRIC_TYPE_INFR_DOTNET_ANON_REQ,
			self::METRIC_TYPE_INFR_NETWORK_IN,
			self::METRIC_TYPE_INFR_NETWORK_OUT
		];
		
		//sort the list
		$aSortedList = [];
		foreach ($aTypes as $sMetricType){
			$oDetails = self::getInfrastructureMetric(null,null,$sMetricType);
			$aSortedList[$oDetails->caption] = $oDetails;
		}
		uksort($aSortedList, "strnatcasecmp");
		$aTypes = [];
		foreach ($aSortedList as $oDetails)
			$aTypes[] = $oDetails->type;
		
		return $aTypes;
	}
	
	//**************************************************************************
	public static function getInfrastructureMetric($psTier, $psNode=null, $psMetricType){			
			switch($psMetricType){
				case cAppDynMetric::METRIC_TYPE_ERRORS:
					if ($psTier)
						$sMetricUrl = cAppDynMetric::tierErrorsPerMin($psTier,$psNode);
					else
						$sMetricUrl = cAppDynMetric::appErrorsPerMin();
					$sCaption = "Errors per min";
					$sShortCaption = "Errors";
					break;
				case cAppDynMetric::METRIC_TYPE_ACTIVITY:
					if ($psTier)
						$sMetricUrl = cAppDynMetric::tierNodeCallsPerMin($psTier,$psNode);
					else
						$sMetricUrl = cAppDynMetric::appCallsPerMin();
					$sCaption = "Calls per min";
					$sShortCaption = "Activity";
					break;
				case cAppDynMetric::METRIC_TYPE_RESPONSE_TIMES:
					if ($psTier)
						$sMetricUrl = cAppDynMetric::tierNodeResponseTimes($psTier,$psNode);
					else
						$sMetricUrl = cAppDynMetric::appResponseTimes();
					$sCaption = "response times in ms";
					$sShortCaption = "Response";
					break;
				case self::METRIC_TYPE_INFR_AVAIL:
					$sMetricUrl = cAppDynMetric::InfrastructureAgentAvailability($psTier,$psNode);
					$sCaption = "Agent Availailability";
					$sShortCaption = "Availability";
					break;
				case self::METRIC_TYPE_INFR_CPU_BUSY:
					$sMetricUrl = cAppDynMetric::InfrastructureCpuBusy($psTier,$psNode);
					$sCaption = "CPU Busy";
					$sShortCaption = "CPU Busy";
					break;
				case self::METRIC_TYPE_INFR_MEM_FREE:
					$sMetricUrl = cAppDynMetric::InfrastructureMemoryFree($psTier,$psNode);
					$sCaption = "Server memory free in MB";
					$sShortCaption = "Server Memory free (MB)";
					break;
				case self::METRIC_TYPE_INFR_NETWORK_IN:
					$sMetricUrl = cAppDynMetric::InfrastructureNetworkIncoming($psTier,$psNode);
					$sCaption = "incoming network traffic in KB/sec ";
					$sShortCaption = "Network-in";
					break;
				case self::METRIC_TYPE_INFR_NETWORK_OUT:
					$sMetricUrl = cAppDynMetric::InfrastructureNetworkOutgoing($psTier,$psNode);
					$sCaption = "outgoing network traffic in KB/sec ";
					$sShortCaption = "Network-out";
					break;
				case self::METRIC_TYPE_INFR_JAVA_HEAP_USED:
					$sMetricUrl = cAppDynMetric::InfrastructureJavaHeapUsed($psTier,$psNode);
					$sCaption = "memory - Java Heap used ";
					$sShortCaption = "Java Heap used";
					break;
				case self::METRIC_TYPE_INFR_JAVA_HEAP_USEDPCT:
					$sMetricUrl = cAppDynMetric::InfrastructureJavaHeapUsedPct($psTier,$psNode);
					$sCaption = "memory - Java Heap %Used ";
					$sShortCaption = "Java Heap %Used";
					break;
				case self::METRIC_TYPE_INFR_JAVA_GC_TIME:
					$sMetricUrl = cAppDynMetric::InfrastructureJavaGCTime($psTier,$psNode);
					$sCaption = "Java GC Time ";
					$sShortCaption = "Java GC Time";
					break;
				case self::METRIC_TYPE_INFR_JAVA_CPU_USAGE:
					$sMetricUrl = cAppDynMetric::InfrastructureJavaCPUUsage($psTier,$psNode);
					$sCaption = "CPU - Java Usage ";
					$sShortCaption = "Java CPU";
					break;
				case self::METRIC_TYPE_INFR_DOTNET_HEAP_USED:
					$sMetricUrl = cAppDynMetric::InfrastructureDotnetHeapUsed($psTier,$psNode);
					$sCaption = "memory - dotNet heap used ";
					$sShortCaption = ".Net heap used";
					break;
				case self::METRIC_TYPE_INFR_DOTNET_GC_PCT:
					$sMetricUrl = cAppDynMetric::InfrastructureDotnetGCTime($psTier,$psNode);
					$sCaption = "percent DotNet GC time  ";
					$sShortCaption = ".Net-GC";
					break;
				case self::METRIC_TYPE_INFR_DOTNET_ANON_REQ:
					$sMetricUrl = cAppDynMetric::InfrastructureDotnetAnonRequests($psTier,$psNode);
					$sCaption = "DotNet Anonymous Requests  ";
					$sShortCaption = ".Net-Anonymous";
					break;
				case self::METRIC_TYPE_INFR_AGENT_METRICS:
					$sMetricUrl = cAppDynMetric::InfrastructureAgentMetricsUploaded($psTier,$psNode);
					$sCaption = "Agent - Metrics uploaded  ";
					$sShortCaption = "Agent-Metrics";
					break;
				case self::METRIC_TYPE_INFR_AGENT_INVALID_METRICS:
					$sMetricUrl = cAppDynMetric::InfrastructureAgentInvalidMetrics($psTier,$psNode);
					$sCaption = "Agent - Invalid Metrics  ";
					$sShortCaption = "Agent-Invalid Metrics";
					break;
				case self::METRIC_TYPE_INFR_AGENT_LICENSE_ERRORS:
					$sMetricUrl = cAppDynMetric::InfrastructureAgentMetricsLicenseErrors($psTier,$psNode);
					$sCaption = "Agent - License Errors ";
					$sShortCaption = "Agent-License errors";
					break;
				default:
					cDebug::error("unknown Metric type");
			}	

			return (object)["metric"=>$sMetricUrl, "caption"=>$sCaption, "short"=>$sShortCaption , "type"=>$psMetricType];
	}
}

//######################################################################
class cAppDynMetric{
	const METRIC_TYPE_QS ="mt";
	const METRIC_TYPE_RUMCALLS = "mrc";
	const METRIC_TYPE_RUMRESPONSE = "mrr";
	const METRIC_TYPE_TRANSRESPONSE = "mtr";
	const METRIC_TYPE_DATABASE_TIME = "mdt";
	const METRIC_TYPE_ACTIVITY = "mac";
	const METRIC_TYPE_ERRORS = "mer";
	const METRIC_TYPE_RESPONSE_TIMES = "mrt";
	const METRIC_TYPE_BACKEND_RESPONSE = "mbr";
	const METRIC_TYPE_BACKEND_ACTIVITY = "mba";
	const METRIC_TYPE_JMX_DBPOOLS = "mtjdbp";
	
	const USAGE_METRIC = "moduleusage";
	const RESPONSE_TIME = "Average Response Time (ms)";
	const CALLS_PER_MIN = "Calls per Minute";
	const ERRS_PER_MIN = "Errors per Minute";
	const SLOW_CALLS = "Number of Slow Calls";
	const VSLOW_CALLS = "Number of Very Slow Calls";
	const STALL_COUNT = "Stall Count";
	
	const APPLICATION = "Overall Application Performance";
	const ERRORS = "Errors";
	const INFRASTRUCTURE = "Application Infrastructure Performance";
	const BACKENDS = "Backends";
	const TRANSACTIONS = "Business Transaction Performance";
	const END_USER = "End User Experience";
	const DATABASES = "Databases";
	const INFORMATION_POINTS = "Information Points";
	const SERVICE_END_POINTS = "Service Endpoints";
	const EXT_CALLS = "External Calls";
	const BASE_PAGES = "Base Pages";
	const AJAX_REQ = "AJAX Requests";
	
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//* Module Usage
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	public static function moduleUsage($psModule, $piMonths){
		return self::USAGE_METRIC."/$psModule/$piMonths";
	}
	
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//* Application
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	public static function appResponseTimes(){
		return self::APPLICATION."|".self::RESPONSE_TIME;
	}
	
	public static function appCallsPerMin(){
		return self::APPLICATION."|".self::CALLS_PER_MIN;
	}

	public static function appSlowCalls(){
		return self::APPLICATION."|".self::SLOW_CALLS;
	}

	public static function appVerySlowCalls(){
		return self::APPLICATION."|".self::VSLOW_CALLS;
	}

	public static function appStalledCount(){
		return self::APPLICATION."|".self::STALL_COUNT;
	}
	public static function appErrorsPerMin(){
		return self::APPLICATION."|".self::ERRS_PER_MIN;
	}
	public static function appExceptionsPerMin(){
		return self::APPLICATION."|Exceptions per Minute";
	}

	public static function appBackends(){
		return self::backends();
	}
	public static function appExtCalls(){
		return self::APPLICATION."|*|External Calls";
	}


	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//* Service End  Points
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	public static function endPointResponseTimes($psTier, $psName){
		return self::SERVICE_END_POINTS."|$psTier|$psName|".self::RESPONSE_TIME;
	}
	
	public static function endPointCallsPerMin($psTier, $psName){
		return self::SERVICE_END_POINTS."|$psTier|$psName|".self::CALLS_PER_MIN;
	}

	public static function endPointErrorsPerMin($psTier, $psName){
		return self::SERVICE_END_POINTS."|$psTier|$psName|".self::ERRS_PER_MIN;
	}
	
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//* backends
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	public static function backends(){
		return self::BACKENDS;
	}
	
	public static function backendResponseTimes($psBackend){
		return self::BACKENDS."|$psBackend|".self::RESPONSE_TIME;
	}

	public static function backendCallsPerMin($psBackend){
		return self::BACKENDS."|$psBackend|".self::CALLS_PER_MIN;
	}
	public static function backendErrorsPerMin($psBackend){
		return self::BACKENDS."|$psBackend|".self::ERRS_PER_MIN;
	}

	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//* Database
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	public static function databases(){
		return self::DATABASES;
	}
	
	public static function databaseKPI($psDB){
		return self::DATABASES."|$psDB|KPI";
	}
	
	public static function databaseTimeSpent($psDB){
		return self::databaseKPI($psDB)."|Time Spent in Executions (s)";
	}
	public static function databaseConnections($psDB){
		return self::databaseKPI($psDB)."|Number of Connections";
	}
	public static function databaseCalls($psDB){
		return self::databaseKPI($psDB)."|".self::CALLS_PER_MIN;
	}

	public static function databaseServerStats($psDB){
		return self::DATABASES."|$psDB|Server Statistic";
	}
	
	
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//* Errors
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	public static function Errors($psTier, $psError){
		return self::ERRORS."|$psTier|$psError|".self::ERRS_PER_MIN;
	}
	
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//* information Points
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	public static function infoPointResponseTimes($psName){
		return self::INFORMATION_POINTS."|$psName|".self::RESPONSE_TIME;
	}
	
	public static function infoPointCallsPerMin($psName){
		return self::INFORMATION_POINTS."|$psName|".self::CALLS_PER_MIN;
	}

	public static function infoPointErrorsPerMin($psName){
		return self::INFORMATION_POINTS."|$psName|".self::ERRS_PER_MIN;
	}
	
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//* infrastructure
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	public static function InfrastructureNodes($psTier){
		return self::INFRASTRUCTURE."|$psTier|Individual Nodes";
	}
	
	public static function InfrastructureNode($psTier, $psNode= null){
		$sMetric = self::INFRASTRUCTURE."|$psTier";
		if ($psNode) $sMetric .= "|Individual Nodes|$psNode";
		
		return $sMetric;
	}
	
	public static function InfrastructureJDBCPools($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|JMX|JDBC Connection Pools";
	}

	public static function InfrastructureJDBCPoolActive($psTier, $psNode=null, $psPool){
		return self::InfrastructureJDBCPools($psTier, $psNode)."|$psPool|Active Connections";
	}
	public static function InfrastructureJDBCPoolMax($psTier, $psNode=null, $psPool){
		return self::InfrastructureJDBCPools($psTier, $psNode)."|$psPool|Maximum Connections";
	}

	public static function InfrastructureNodeDisks($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|Hardware Resources|Disks";
	}
	public static function InfrastructureNodeDiskFree($psTier, $psNode, $psDisk){
		return self::InfrastructureNodeDisks($psTier, $psNode)."|$psDisk|Space Available";
	}
	public static function InfrastructureNodeDiskUsed($psTier, $psNode, $psDisk){
		return self::InfrastructureNodeDisks($psTier, $psNode)."|$psDisk|Space Used";
	}
	
	public static function InfrastructureAgentAvailability($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|Agent|Machine|Availability";
	}

	public static function InfrastructureAgentMetricsUploaded($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|Agent|Metric Upload|Metrics uploaded";
	}

	public static function InfrastructureAgentMetricsLicenseErrors($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."Agent|Metric Upload|Requests License Errors";
	}

	public static function InfrastructureAgentInvalidMetrics($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."Agent|Metric Upload|Invalid Metrics";
	}

	public static function InfrastructureMachineAvailability($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|Hardware Resources|Machine|Availability";
	}

	public static function InfrastructureCpuBusy($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|Hardware Resources|CPU|%Busy";
	}

	public static function InfrastructureMemoryFree($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|Hardware Resources|Memory|Free (MB)";
	}

	public static function InfrastructureDiskFree($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|Hardware Resources|Disks|MB Free";
	}
	
	public static function InfrastructureDiskWrites($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|Hardware Resources|Disks|KB written/sec";
	}

	public static function InfrastructureNetworkIncoming($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|Hardware Resources|Network|Incoming KB/sec";
	}
	public static function InfrastructureNetworkOutgoing($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|Hardware Resources|Network|Outgoing KB/sec";
	}
	
	public static function InfrastructureJavaHeapUsed($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|JVM|Memory|Heap|Current Usage (MB)";
	}
	public static function InfrastructureJavaHeapUsedPct($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|JVM|Memory|Heap|Used %";
	}

	public static function InfrastructureJavaGCTime($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|JVM|Garbage Collection|GC Time Spent Per Min (ms)";
	}
	public static function InfrastructureJavaCPUUsage($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|JVM|Process CPU Usage %";
	}
	public static function InfrastructureDotnetHeapUsed($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|CLR|Memory|Heap|Current Usage (bytes)";
	}
	public static function InfrastructureDotnetGCTime($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|CLR|Garbage Collection|GC Time Spent (%)";
	}
	public static function InfrastructureDotnetAnonRequests($psTier, $psNode=null){
		return self::InfrastructureNode($psTier, $psNode)."|ASP.NET Applications|Anonymous Requests";
	}
	
	public static function InfrastructureMetric($psTier, $psNode, $psMetric){
		return self::InfrastructureNode($psTier, $psNode)."|$psMetric";
	}
	
	
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//* tiers
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	public static function tier($psTier){
		return self::TRANSACTIONS."|Business Transactions|$psTier";
	}
	
	public static function tierCallsPerMin($psTier){
		return self::APPLICATION."|$psTier|".self::CALLS_PER_MIN;
	}
	public static function tierResponseTimes($psTier){
		return self::APPLICATION."|$psTier|".self::RESPONSE_TIME;
	}
	public static function tierErrorsPerMin($psTier){
		return self::APPLICATION."|$psTier|".self::ERRS_PER_MIN;
	}
	public static function tierExceptionsPerMin($psTier){
		return self::APPLICATION."|$psTier|Exceptions per Minute";
	}
	public static function tierSlowCalls($psTier){
		return self::APPLICATION."|$psTier|".self::SLOW_CALLS;
	}
	
	public static function tierNodes($psTier){
		return self::APPLICATION."|$psTier|Individual Nodes";
	}

	public static function tierVerySlowCalls($psTier){
		return self::APPLICATION."|$psTier|".self::VSLOW_CALLS;
	}
	public static function tierTransactions($psTier){
		$sMetric = self::TRANSACTIONS."|Business Transactions|$psTier";
		return $sMetric;
	}
	
	public static function tierExt($psTier1,$psTier2){
		return self::APPLICATION."|$psTier1|".self::EXT_CALLS."|$psTier2";
	}
	
	public static function tierExtCallsPerMin($psTier1,$psTier2){
		return self::tierExt($psTier1,$psTier2)."|".self::CALLS_PER_MIN;
	}

	public static function tierExtResponseTimes($psTier1,$psTier2){
		return self::tierExt($psTier1,$psTier2)."|".self::RESPONSE_TIME;
	}
	public static function tierExtErrorsPerMin($psTier1,$psTier2){
		return self::tierExt($psTier1,$psTier2)."|".self::ERRS_PER_MIN;
	}

	public static function tierNodeCallsPerMin($psTier, $psNode=null){
		if ($psNode)
			return self::tierNodes($psTier)."|$psNode|".self::CALLS_PER_MIN;
		else
			return self::tierCallsPerMin($psTier);
	}
	
	public static function tierNodeResponseTimes($psTier, $psNode=null){
		if ($psNode)
			return self::tierNodes($psTier)."|$psNode|".self::RESPONSE_TIME;
		else
			return self::tierResponseTimes($psTier);
	}
	
	public static function tierServiceEndPoints($psTier){
		return self::SERVICE_END_POINTS. "|$psTier";
	}

	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//* transactions
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	public static function transMetric($psTier, $psTrans, $psNode=null){
		$sMetric = self::tierTransactions($psTier)."|$psTrans";
		if ($psNode) $sMetric .= "|Individual Nodes|$psNode";
		return $sMetric;
	}
	
	public static function transResponseTimes($psTier, $psTrans, $psNode=null){
		return self::transMetric($psTier, $psTrans, $psNode)."|".self::RESPONSE_TIME;
	}

	public static function transErrors($psTier, $psTrans, $psNode=null){
		return self::transMetric($psTier, $psTrans, $psNode)."|".self::ERRS_PER_MIN;
	}

	public static function transCpuUsed($psTier, $psTrans, $psNode=null){
		return self::transMetric($psTier, $psTrans, $psNode)."|Average CPU Used (ms)";
	}
	
	public static function transCallsPerMin($psTier, $psTrans, $psNode=null){
		return self::transMetric($psTier, $psTrans, $psNode)."|".self::CALLS_PER_MIN;
	}
	
	public static function transExtNames($psTier, $psTrans, $psNode=null){
		return self::transMetric($psTier, $psTrans, $psNode)."|".self::EXT_CALLS;
	}
	
	public static function transExtCalls($psTier, $psTrans, $psOther){
		return self::transExtNames($psTier, $psTrans)."|$psOther|".self::CALLS_PER_MIN;
	}
		
	public static function transExtResponseTimes($psTier, $psTrans, $psOther){
		return self::transExtNames($psTier, $psTrans)."|$psOther|".self::RESPONSE_TIME;
	}
	public static function transExtErrors($psTier, $psTrans, $psOther){
		return self::transExtNames($psTier, $psTrans)."|$psOther|".self::ERRORS;
	}
}

class cAppDynWebRumMetric{
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	//* webrum
	//>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	public static function jobs(){
		return cAppDynMetric::END_USER."|Synthetic Jobs";
	}
	public static function App(){
		return cAppDynMetric::END_USER."|App";
	}
	public static function CallsPerMin(){
		return self::App()."|Page Requests per Minute";
	}
	public static function ResponseTimes(){
		return self::App()."|End User Response Time (ms)";
	}
	public static function JavaScriptErrors(){
		return self::App()."|Page views with JavaScript Errors per Minute";
	}
	public static function FirstByte(){
		return self::App()."|First Byte Time (ms)";
	}
	public static function ServerTime(){
		return self::App()."|Application Server Time (ms)";
	}
	public static function TCPTime(){
		return self::App()."|TCP Connect Time (ms)";
	}

	public static function Ajax(){
		return cAppDynMetric::END_USER."|AJAX Requests";
	}
	public static function Pages(){
		return cAppDynMetric::END_USER."|Base Pages";
	}
	
	public Static function Metric($psKind, $psPage, $psMetric)
	{
		switch ($psKind){
			case cAppDynMetric::BASE_PAGES:
			case cAppDynMetric::AJAX_REQ:
				break;
			default:
				cDebug::error("unknown kind");
		}
		return cAppDynMetric::END_USER."|$psKind|$psPage|$psMetric";
	}
	
	
	public static function PageCallsPerMin($psType, $psPage){
		return self::Metric($psType, $psPage, "Requests per Minute");
	}
	public static function PageResponseTimes($psType, $psPage){
		return self::Metric($psType, $psPage, "End User Response Time (ms)");
	}
	public static function PageFirstByte($psType, $psPage){
		return self::Metric($psType, $psPage, "First Byte Time (ms)");
	}
	public static function PageServerTime($psType, $psPage){
		return self::Metric($psType, $psPage, "Application Server Time (ms)");
	}
	public static function PageTCPTime($psType, $psPage){
		return self::Metric($psType, $psPage, "TCP Connect Time (ms)");
	}
	public static function PageJavaScriptErrors($psType, $psPage){
		return self::Metric($psType, $psPage, "Page views with JavaScript Errors per Minute");
	}

}
?>
