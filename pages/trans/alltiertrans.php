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
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/inc-charts.php";

require_once("$root/inc/inc-filter.php");

//####################################################################
cRenderHtml::header("All Tier Transactions");
cRender::force_login();
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE/2;
	
//###################### DATA #############################################################
//display the results
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;

$node= cHeader::get(cRender::NODE_QS);
$gsAppQs=cRenderQS::get_base_app_QS($oApp);
$gsTierQs=cRenderQS::get_base_tier_QS($oTier);
$gsMetric = cHeader::get(cRender::METRIC_QS);

$title= "$oApp->name&gt;$oTier->name&gt;All Transactions";
cRender::show_time_options( $title); 

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRenderHtml::footer();
	exit;
}

//********************************************************************
$oCred = cRenderObjs::get_appd_credentials();
cRenderMenus::show_tier_functions();

//###############################################
?>
<h2>Overall Stats for <?=cRender::show_name(cRender::NAME_TIER,$oTier)?></h2>
<?php
	$sBaseUrl = cHttp::build_url("alltiertrans.php",$gsTierQs);
	$aMetrics=[];
	
	$sMetricUrl=cAppDynMetric::tierCallsPerMin($oTier->name);
	$sUrl = cHttp::build_url($sBaseUrl, cRender::METRIC_QS, cAppDynMetric::CALLS_PER_MIN );
	$aMetrics[] = [
		cChart::LABEL=>"Overall Calls per min ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"All Transactions"
	];
	
	$sMetricUrl=cAppDynMetric::tierResponseTimes($oTier->name);
	$sUrl = cHttp::build_url($sBaseUrl, cRender::METRIC_QS, cAppDynMetric::RESPONSE_TIME );
	$aMetrics[] = [
		cChart::LABEL=>"Overall response times (ms) ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"All Transactions"
	];
	
	$sMetricUrl=cAppDynMetric::tierErrorsPerMin($oTier->name);
	$sUrl = cHttp::build_url($sBaseUrl, cRender::METRIC_QS, cAppDynMetric::ERRS_PER_MIN );
	$aMetrics[] = [
		cChart::LABEL=>"Errors($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"All Transactions"
	];
	
	$sMetricUrl = cAppDynMetric::InfrastructureCpuBusy($oTier->name);
	$aMetrics[] = [
		cChart::LABEL=>"CPU($oTier->name)tier", cChart::METRIC=>$sMetricUrl
	];
	
	cChart::metrics_table($oApp,$aMetrics,4,cRender::getRowClass());


//################################################################################################
?><p><h2>All Transactions: <?=$gsMetric?></h2><?php
// get all transactions for tier
function sort_by_metricpath($a,$b){
	return strcasecmp($a->metricPath, $b->metricPath);
}

$sMetricpath = cAppdynMetric::transResponseTimes($oTier->name, "*");
$oTimes = cRender::get_times();
$aStats = cAppdynCore::GET_MetricData($oApp, $sMetricpath, $oTimes,"true",false,true);
uasort($aStats,"sort_by_metricpath" );
$sBaseUrl = cHttp::build_url("transdetails.php", $gsTierQS);

$aMetrics = [];
foreach ($aStats as $oTrans){
	$sTrName = cAppdynUtil::extract_bt_name($oTrans->metricPath, $oTier->name);
	try{
		$sTrID = cAppdynUtil::extract_bt_id($oTrans->metricName);
	}
	catch (Exception $e){
		$sTrID = null;
	}
	$sLink = cHttp::build_url($sBaseUrl,cRender::TRANS_QS, $sTrName);
	$sLink = cHttp::build_url($sLink,cRender::TRANS_ID_QS,$sTrID);
		
	$sMetric = cAppDynMetric::transMetric($oTier->name, $sTrName)."|$gsMetric";
	$aMetrics[] = [
		cChart::LABEL=>"$sTrName - $gsMetric", cChart::METRIC=>$sMetric, cChart::HIDEIFNODATA=>1,
		cChart::GO_URL=>$sLink, cChart::GO_HINT=>"Go"
	];
}
cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);			




//###############################################
cChart::do_footer();
cRenderHtml::footer();
?>