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


//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("All Tier Transactions");
cRender::force_login();
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE/2;
	
//###################### DATA #############################################################
//display the results
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;

$node= cHeader::get(cRenderQS::NODE_QS);
$gsAppQs=cRenderQS::get_base_app_QS($oApp);
$gsTierQs=cRenderQS::get_base_tier_QS($oTier);
$gsMetric = cHeader::get(cRenderQS::METRIC_QS);

$title= "$oApp->name&gt;$oTier->name&gt;All Transactions";

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

//********************************************************************
$oCred = cRenderObjs::get_AD_credentials();
cRenderMenus::show_tier_functions();

//###############################################
?>
<h2>Overall Stats for <?=$oTier->name?></h2>
<?php
	$sBaseUrl = cHttp::build_url(cCommon::filename(),$gsTierQs);
	$aMetrics=[];
	
	$sMetricUrl=cADTierMetricPaths::tierCallsPerMin($oTier->name);
	$sUrl = cHttp::build_url($sBaseUrl, cRenderQS::METRIC_QS, cADMetricPaths::CALLS_PER_MIN );
	$aMetrics[] = [
		cChart::LABEL=>"Overall Calls per min ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"All Transactions"
	];
	
	$sMetricUrl=cADTierMetricPaths::tierResponseTimes($oTier->name);
	$sUrl = cHttp::build_url($sBaseUrl, cRenderQS::METRIC_QS, cADMetricPaths::RESPONSE_TIME );
	$aMetrics[] = [
		cChart::LABEL=>"Overall response times (ms) ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"All Transactions"
	];
	
	$sMetricUrl=cADTierMetricPaths::tierErrorsPerMin($oTier->name);
	$sUrl = cHttp::build_url($sBaseUrl, cRenderQS::METRIC_QS, cADMetricPaths::ERRS_PER_MIN );
	$aMetrics[] = [
		cChart::LABEL=>"Errors($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"All Transactions"
	];
	
	$sMetricUrl = cADInfraMetric::InfrastructureCpuBusy($oTier->name);
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

$oTrans = new cADBT( $oTier, "*", null, true);
$sMetricpath = cADMetricPaths::transResponseTimes($oTrans);
$oTimes = cRender::get_times();
$aStats = $oApp->GET_MetricData($sMetricpath, $oTimes,true,false,true);
uasort($aStats,"sort_by_metricpath" );
$sBaseUrl = cHttp::build_url("transdetails.php", $gsTierQS);

$aMetrics = [];
foreach ($aStats as $oTrans){
	$sTrName = cADUtil::extract_bt_name($oTrans->metricPath, $oTier->name);
	try{
		$sTrID = cADUtil::extract_bt_id($oTrans->metricName);
	}
	catch (Exception $e){
		$sTrID = null;
	}
	$sLink = cHttp::build_url($sBaseUrl,cRenderQS::TRANS_QS, $sTrName);
	$sLink = cHttp::build_url($sLink,cRenderQS::TRANS_ID_QS,$sTrID);
		
	$sMetric = cADMetricPaths::transaction($oTier->name, $sTrName)."|$gsMetric";
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