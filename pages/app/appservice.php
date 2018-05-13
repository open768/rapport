<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
//####################################################################
$home = "../..";
$root=realpath($home);
$phpinc = realpath("$root/../phpinc");
$jsinc = "$home/../jsinc";

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");

set_time_limit(200); 

//####################################################################
cRender::html_header("Service End Points");
cRender::force_login();
cChart::do_header();

//####################################################################
//get passed in values
$oApp = cRenderObjs::get_current_app();
$oTimes = cRender::get_times();

$title= "$oApp->name&gt;Service EndPoints";
cRender::show_time_options($title); 
cRenderMenus::show_apps_menu("Show Service EndPoints for", "appservice.php");
if (cFilter::isFiltered()){
	$sCleanAppQS = cRender::get_clean_base_app_QS();
	cRender::button("Clear Filter", "appservice.php?$sCleanAppQS");
}
//####################################################################
//retrieve tiers
//********************************************************************
$aTiers = $oApp->GET_Tiers();

function pr__sort_endpoints($a,$b){
	return strcmp($a->name, $b->name);
}

foreach ($aTiers as $oTier){
	if (cFilter::isTierFilteredOut($oTier)) continue;

	//****************************************************************************************
	$aEndPoints = $oTier->GET_ServiceEndPoints();
	if (count($aEndPoints) == 0){
		cRender::messagebox("no Service endpoints found for $oTier->name");
		continue;
	}
	uasort($aEndPoints, "pr__sort_endpoints");

	//****************************************************************************************
	?><p><?php
	cRenderMenus::show_tier_functions($oTier);
	$aHeaders = ["End Point","Activity","Response Times in ms","Errors per minute"];
	$aMetrics = [];
	foreach ($aEndPoints as $oEndPoint){
		$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>$oEndPoint->name];
		$aMetrics[] = [cChart::LABEL=>"Calls", cChart::METRIC=>cAppdynMetric::endPointCallsPerMin($oTier->name, $oEndPoint->name)];
		$aMetrics[] = [cChart::LABEL=>"Response", cChart::METRIC=>cAppdynMetric::endPointResponseTimes($oTier->name, $oEndPoint->name)];
		$aMetrics[] = [cChart::LABEL=>"Errors", cChart::METRIC=>cAppdynMetric::endPointErrorsPerMin($oTier->name, $oEndPoint->name)];
	}
	$sClass = cRender::getRowClass();
	cChart::metrics_table($oApp,$aMetrics,4,$sClass,null,cChart::CHART_WIDTH_LETTERBOX/3, $aHeaders);
	cDebug::flush();
}

//####################################################################
cChart::do_footer();

cRender::html_footer();
?>
