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
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";



//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();
$gsAppQS = cRenderQS::get_base_app_QS($oApp);
$oApp = cRenderObjs::get_current_app();
$sGraphUrl = cHttp::build_url("rumstats.php", $gsAppQS);

//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("Web browser - Real user monitoring - graphs");
cChart::do_header();

cRender::force_login();
$title ="$oApp->name&gtWeb Real User Monitoring Stats";

cRenderMenus::show_app_change_menu("Show Stats for:");
$oTimes = cRender::get_times();
cRender::button("Statistics", $sGraphUrl);	
cADCommon::button(cADControllerUI::webrum_pages($oApp));

//#############################################################
function sort_metric_names($poRow1, $poRow2){
	return strnatcasecmp($poRow1->metricPath, $poRow2->metricPath);
}

$gsTABLE_ID = 0;

//*****************************************************************************
function render_graphs($psType, $paData){
	global $oApp, $gsAppQS;
	
	uasort ($paData, "sort_metric_names");
	$aMetrics = [];
	
	$sBaseQS = cHttp::build_QS($gsAppQS, cRenderQS::RUM_TYPE_QS,$psType);
				
	foreach ($paData as $oItem){
		if ($oItem == null ) continue;
		if ($oItem->metricValues == null ) continue;
		
		$oValues = $oItem->metricValues[0];
		if ($oValues->count == 0 ) continue;

		$sName = cADUtil::extract_RUM_name($psType, $oItem->metricPath);
		$sRumId = cADUtil::extract_RUM_id($psType, $oItem->metricName);
		$sDetailQS = cHttp::build_QS($sBaseQS, cRenderQS::RUM_PAGE_QS,$sName);
		$sDetailQS = cHttp::build_QS($sDetailQS, cRenderQS::RUM_PAGE_ID_QS,$sRumId);
		$sUrl = "rumpage.php?$sDetailQS";

		$aMetrics[] = [
			cChart::LABEL=>$sName ." Response times (ms)", cChart::METRIC=>$oItem->metricPath,
			cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See details" 
		];

	}
	cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
}

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************


//#############################################################
//get the page metrics
?>
<h2>Page Requests</h2>
<?php
	$sMetricpath = cADWebRumMetricPaths::PageResponseTimes(cADMetricPaths::BASE_PAGES, "*");
	$aData = $oApp->GET_MetricData($sMetricpath, $oTimes,true,false,true);
	render_graphs(cADMetricPaths::BASE_PAGES, $aData);
	
// ############################################################
?>
<h2>Ajax Requests</h2>
<?php
	$sMetricpath = cADWebRumMetricPaths::PageResponseTimes(cADMetricPaths::AJAX_REQ, "*");
	$aData = $oApp->GET_MetricData($sMetricpath, $oTimes,true,false,true);
	render_graphs(cADMetricPaths::AJAX_REQ, $aData);

	// ############################################################
cChart::do_footer();
cRenderHtml::footer();
?>
