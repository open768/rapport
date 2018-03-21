<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

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


set_time_limit(200); // huge time limit as this could takes a long time

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/header.php");
require_once("../$home/inc/inc-render.php");

cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();


//###################### DATA #############################################################
$oApp = cRender::get_current_app();
$tier = cHeader::get(cRender::TIER_QS);
$trans = cHeader::get(cRender::TRANS_QS);
$index = cHeader::get("id"); //id of HTML div

$aResult = array( "id"=>$index);

$oTimes = cRender::get_times();
$sMetricpath = cAppdynMetric::transResponseTimes($tier, $trans);
$aStats = cAppdynCore::GET_MetricData($oApp->name, $sMetricpath, $oTimes,"true",false,true);


if ($aStats){
	$aResult["max"] = $aStats[0];
	$sMetricpath = cAppdynMetric::transErrors($tier, $trans);
	$aErrors = cAppdynCore::GET_MetricData($oApp->name, $sMetricpath, $oTimes,"true",false,true);
	
	if ($aErrors)		$aResult["transErrors"] = $aErrors[0];
}else{
	$aResult["error"] = "no data";
}

echo json_encode($aResult);	
?>
