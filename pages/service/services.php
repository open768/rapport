<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";

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

cRenderMenus::show_apps_menu("Show Service EndPoints for", "services.php");
cADCommon::button(cADControllerUI::serviceEndPoints($oApp,$oTimes));

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
	$aEndPoints = cAD_RestUI::GET_service_end_points($oTier);
	//$aEndPoints = $oTier->GET_ServiceEndPoints();
	if (count($aEndPoints) == 0){
		cCommon::messagebox("no Service endpoints found for $oTier->name");
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
		$aMetrics[] = [cChart::LABEL=>"Calls", cChart::METRIC=>cADMetric::endPointCallsPerMin($oTier->name, $oEndPoint->name), cChart::GO_URL=>$sUrl];
		$aMetrics[] = [cChart::LABEL=>"Response", cChart::METRIC=>cADMetric::endPointResponseTimes($oTier->name, $oEndPoint->name)];
		$aMetrics[] = [cChart::LABEL=>"Errors", cChart::METRIC=>cADMetric::endPointErrorsPerMin($oTier->name, $oEndPoint->name)];
	}
	$sClass = cRender::getRowClass();
	cChart::metrics_table($oApp,$aMetrics,4,$sClass,null,(cChart::CHART_WIDTH_LETTERBOX-150)/3, $aHeaders);
	cDebug::flush();
}

//####################################################################
cChart::do_footer();

cRenderHtml::footer();
?>
