<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

$home="..";
require_once "$home/inc/common.php";


//###################### DATA #############################################
$oApp = cRenderObjs::get_current_app();
$sBaseMetric = cHeader::get(cRenderQS::METRIC_QS);

//*************************************************************************
cDebug::write("getting stats $oApp->name");
$oTimes = new cADTimes;
$oTimes->start = cHeader::get(cRenderQS::TIME_START_QS);
$oTimes->end = cHeader::get(cRenderQS::TIME_END_QS);
$oTimes->time_type = cADTimes::BETWEEN;

cDebug::extra_debug($oTimes->toString());

class cWidget_output{
	public $start_date;
	public $end_date;
	public $sum_calls;
	public $calls_per_min;
	public $avg_response_time;
	public $max_response_time;
	public $sum_errors;
	public $error = null;
}

//*************************************************************************
//* output
//*************************************************************************
//calls per min
$oOutput = new cWidget_output;

cDebug::write("getting calls");
$sMetric = $sBaseMetric."|".cADMetricPaths::CALLS_PER_MIN;

//if the year is last year round the start time down to the hour
$dStart = $oTimes->start_time();
$dEnd = $oTimes->end_time();
$dNow = new DateTime;
if ($dStart->format("Y") <> $dNow->format("Y")){
	cDebug::extra_debug("start time not the same year ".$dStart->format(cCommon::PHP_UK_DATE_FORMAT));
	$sTime = $dStart->format('d-m-Y H').":00";
	$iTime = strtotime($sTime);
	$oTimes->start = $iTime *1000;
}
if ($dEnd->format("Y") <> $dNow->format("Y")){
	cDebug::extra_debug("start time not the same year ".$dEnd->format(cCommon::PHP_UK_DATE_FORMAT));
	$sTime = $dEnd->format('d-m-Y H').":00";
	$iTime = strtotime($sTime);
	$oTimes->end = $iTime *1000;
}
$oOutput->start_date = $oTimes->start_time()->format(cCommon::PHP_UK_DATE_FORMAT);
$oOutput->end_date = $oTimes->end_time()->format(cCommon::PHP_UK_DATE_FORMAT);

$oData = cADMetricData::GET_MetricData($oApp, $sMetric,$oTimes,true);
if (count($oData) == 0){
	$oOutput->error = "No data for: ".$oTimes->start_time()->format(cCommon::PHP_UK_DATE_FORMAT);
}else{
	$oOutput->sum_calls = $oData[0]->sum;
	$oOutput->calls_per_min = $oData[0]->value;

	//errors per min
	cDebug::write("getting errors");
	$sMetric = $sBaseMetric."|".cADMetricPaths::ERRS_PER_MIN;
	$oData = cADMetricData::GET_MetricData($oApp, $sMetric,$oTimes,true);
	if (count($oData) > 0)
		$oOutput->sum_errors = $oData[0]->sum;
	else
		$oOutput->sum_errors = 0;

	//response times
	cDebug::write("getting response times");
	$sMetric = $sBaseMetric."|".cADMetricPaths::RESPONSE_TIME;
	$oData = cADMetricData::GET_MetricData($oApp, $sMetric,$oTimes,true);
	$oOutput->avg_response_time = $oData[0]->value;
	$oOutput->max_response_time = $oData[0]->max;
}
cDebug::write("outputting json");
cCommon::write_json($oOutput);	
return;
?>
