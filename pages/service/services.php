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
require_once("../../inc/root.php");
cRoot::set_root("../..");
require_once("$root/inc/common.php");
require_once("$root/inc/inc-charts.php");

set_time_limit(200); 

//####################################################################
cRenderHtml::header("Service End Points");
cRender::force_login();
cChart::do_header();

//####################################################################
//get passed in values
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$oTimes = cRender::get_times();

$title= "$oApp->name&gt;Service EndPoints";
cRender::show_time_options($title); 
cRenderMenus::show_apps_menu("Show Service EndPoints for", "services.php");
cRender::appdButton(cAppDynControllerUI::serviceEndPoints($oApp,$oTimes));

//####################################################################
//retrieve tiers
//********************************************************************
if ($oTier->name)
	$aTiers = [ $oTier];
else
	$aTiers = $oApp->GET_Tiers();

function pr__sort_endpoints($a,$b){
	return strcmp($a->name, $b->name);
}

foreach ($aTiers as $oTier){

	//****************************************************************************************
	$aEndPoints = cAppDynRestUI::GET_service_end_points($oTier);
	//$aEndPoints = $oTier->GET_ServiceEndPoints();
	if (count($aEndPoints) == 0){
		cRender::messagebox("no Service endpoints found for $oTier->name");
		continue;
	}
	uasort($aEndPoints, "pr__sort_endpoints");
	$sTierQS = cRenderQS::get_base_tier_QS($oTier);

	//****************************************************************************************
	?><p><?php
	cRenderMenus::show_tier_functions($oTier);
	$aHeaders = ["End Point","Activity","Response Times in ms","Errors per minute"];
	$aMetrics = [];
	foreach ($aEndPoints as $oEndPoint){
		$sUrl = cHttp::build_qs($sTierQS, cRender::SERVICE_QS, $oEndPoint->name);
		$sUrl = cHttp::build_qs($sUrl, cRender::SERVICE_ID_QS, $oEndPoint->id);
		$sUrl = cHttp::build_url("$home/pages/service/endpoint.php", $sUrl);

		$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>$oEndPoint->name, cChart::WIDTH=>150];
		$aMetrics[] = [cChart::LABEL=>"Calls", cChart::METRIC=>cAppdynMetric::endPointCallsPerMin($oTier->name, $oEndPoint->name), cChart::GO_URL=>$sUrl];
		$aMetrics[] = [cChart::LABEL=>"Response", cChart::METRIC=>cAppdynMetric::endPointResponseTimes($oTier->name, $oEndPoint->name)];
		$aMetrics[] = [cChart::LABEL=>"Errors", cChart::METRIC=>cAppdynMetric::endPointErrorsPerMin($oTier->name, $oEndPoint->name)];
	}
	$sClass = cRender::getRowClass();
	cChart::metrics_table($oApp,$aMetrics,4,$sClass,null,(cChart::CHART_WIDTH_LETTERBOX-150)/3, $aHeaders);
	cDebug::flush();
}

//####################################################################
cChart::do_footer();

cRenderHtml::footer();
?>
