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
set_time_limit(200); // huge time limit as this takes a long time

//display the results
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;

$gsTierQs = cRenderQS::get_base_tier_QS($oTier);

//################### CHART HEADER ########################################
cRenderHtml::header("External tier calls");
cRender::force_login();
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE/2;

//###################### DATA ########################################
$title = "$oApp->name&gt;$oTier->name&gt;External Calls";

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************


$oCred = cRenderObjs::get_AD_credentials();
if ($oCred->restricted_login == null){
	cRenderMenus::show_app_functions();
	cRenderMenus::show_tier_functions();
	cRenderMenus::show_tier_menu("Change Tier to", "tierextgraph.php");
}
cRender::button("show as table", "tierextcalls.php?$gsTierQs");
cADCommon::button(cADControllerUI::tier_slow_remote($oApp, $oTier),"Slow Remote Calls");

//************* basic information about the tier *********************
?>
<h2>External calls made from <?=cRender::show_name(cRender::NAME_TIER,$oTier)?> tier</h2>
<h3>Overall Stats for tier</h3>
<?php
	
	$aMetrics=[];
	$sMetricUrl=cADMetric::tierCallsPerMin($oTier->name);
	$aMetrics[] = [cChart::LABEL=>"Overall Calls per min for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cADMetric::tierResponseTimes($oTier->name);
	$aMetrics[] = [cChart::LABEL=>"Overall  response times in ms for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cADMetric::tierErrorsPerMin($oTier->name);
	$aMetrics[] = [cChart::LABEL=>"Error rates for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl];
	cChart::metrics_table($oApp,$aMetrics,3,cRender::getRowClass());


?><h3>External calls</h3><?php
	$linkUrl = cHttp::build_url("tierextalltrans.php", $gsTierQs);
	$oResponse = $oTier->GET_ext_calls();
	
	$aMetrics=[];
	
	foreach ($oResponse as $oExt){
	
		$sTierTo = $oExt->name;
		$sUrl = cHttp::build_url($linkUrl, cRender::BACKEND_QS, $sTierTo);
		$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>$sTierTo, cChart::WIDTH=>300];
		$sMetric=cADMetric::tierExtCallsPerMin($oTier->name, $sTierTo);
		$aMetrics[] = [cChart::LABEL=>"Calls per min", cChart::METRIC=>$sMetric, cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"Drill down"];
		$sMetric=cADMetric::tierExtResponseTimes($oTier->name, $sTierTo);
		$aMetrics[] = [cChart::LABEL=>"Response Times in ms", cChart::METRIC=>$sMetric];
		$sMetric=cADMetric::tierExtErrorsPerMin($oTier->name, $sTierTo);
		$aMetrics[] = [cChart::LABEL=>"Errors Per minuts", cChart::METRIC=>$sMetric];
	}
	cChart::metrics_table($oApp,$aMetrics,4,cRender::getRowClass());

//################ CHART
cChart::do_footer();

cRenderHtml::footer();
?>