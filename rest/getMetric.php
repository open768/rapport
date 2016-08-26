<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2016 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

$root=realpath("..");
$phpinc = realpath("$root/../phpinc");
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("$phpinc/appdynamics/metrics.php");
require_once("$phpinc/appdynamics/account.php");


set_time_limit(200); // huge time limit as this could takes a long time

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/header.php");
require_once("../inc/inc-render.php");
require_once("../inc/inc-metrics.php");
	
//###################### DATA #############################################
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();


//###################### DATA #############################################
$app = cHeader::get(cRender::APP_QS) ;
if (!$app) $app = "no application set";
$psMetric = cHeader::get(cRender::METRIC_QS);
$psDiv = cHeader::get(cRender::DIV_QS);
$psCSV=cHeader::get(cRender::CSV_QS);
$psPrevious = cHeader::get(cRender::PREVIOUS_QS);

//*************************************************************************
cDebug::write("getting metric - $psMetric");
$oResult = cMetric::get_metric($app, $psMetric, ($psPrevious != null));
cDebug::write("got metric - $psMetric");
if ($oResult) $oResult->div = $psDiv;

//*************************************************************************
//* output
//*************************************************************************
if (!$oResult){
	$oResult = ['error'=>"no data", 'div' =>$psDiv];
	cCommon::write_json($oResult);	
	return;
}

if (!$psCSV){
	cDebug::write("outputting json");
	cCommon::write_json($oResult);	
	return;
}

//*************************************************************************
//* CSV
//*************************************************************************
$sFilename = str_replace("/","_",$psMetric);
cHeader::set_download_filename("$sFilename.csv");

cCommon::echo("controller,". cAppdynCore::GET_controller());
cCommon::echo("Application,$app");
cCommon::echo("Date now,".date(DateTime::W3C,time()));
cCommon::echo("metric,$psMetric");
cCommon::echo("");

cCommon::echo("Date,Average Value, Max");
foreach ($oResult->data as $oItem){
	//reformat the date
	$oDate = DateTime::createFromFormat(DateTime::W3C, $oItem->date);
	$sDate = $oDate->format(cCommon::EXCEL_DATE_FORMAT);
	cCommon::echo("$sDate,$oItem->value,$oItem->max");
}
?>
