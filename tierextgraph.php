<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2016 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
$root=realpath(".");
$phpinc = realpath("$root/../phpinc");
$jsinc = "../jsinc";

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
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


//####################################################################
set_time_limit(200); // huge time limit as this takes a long time

//display the results
$oApp = cRender::get_current_app();
$oTier = cRender::get_current_tier();

$gsAppQs = cRender::get_base_app_QS();
$gsTierQs = cRender::get_base_tier_QS();

//################### CHART HEADER ########################################
cRender::html_header("External tier calls");
cRender::force_login();
cChart::do_header();
cChart::$width=cRender::CHART_WIDTH_LARGE/2;

//###################### DATA ########################################
$title = "$oApp->name&gt;$oTier->name&gt;External Calls";
$oTier->nameQS = cRender::get_base_tier_QS();

cRender::show_time_options($title); 
$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null){
	cRenderMenus::show_app_functions();
	cRenderMenus::show_tier_functions();
	cRenderMenus::show_tier_menu("Change Tier to", "tierextgraph.php");
}
cRender::appdButton(cAppDynControllerUI::tier_slow_remote($oApp, $oTier),"Slow Remote Calls");

//************* basic information about the tier *********************
?>
<h2>External calls made from (<?=$oTier->name?>) tier</h2>
<h3>Overall Stats for tier</h3>
<?php
	//********************************************************************
	if (cAppdyn::is_demo()){
		cRender::errorbox("function not support ed for Demo");
		exit;
	}
	//********************************************************************
	
	$aMetrics=[];
	$sMetricUrl=cAppDynMetric::tierCallsPerMin($oTier->name);
	$aMetrics[] = [cChart::LABEL=>"Overall Calls per min for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cAppDynMetric::tierResponseTimes($oTier->name);
	$aMetrics[] = [cChart::LABEL=>"Overall  response times in ms for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl];
	cChart::metrics_table($oApp,$aMetrics,2,cRender::getRowClass());


	$oResponse = cAppdyn::GET_tier_ExtCalls_Metric_heirarchy($oApp->name, $oTier->name);
	$linkUrl = cHttp::build_url("tiertotier.php", $gsAppQs);
	$linkUrl = cHttp::build_url($linkUrl, cRender::FROM_TIER_QS, $oTier->name);
	$linkUrl = cHttp::build_url($linkUrl, cRender::TIER_ID_QS, $oTier->id);
?><h3>External calls</h3><?php
	$aMetrics=[];
	
	foreach ($oResponse as $oDetail){
		$sTierTo = $oDetail->name;
		$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>$sTierTo, cChart::WIDTH=>300];
		$sMetric=cAppDynMetric::tierExtCallsPerMin($oTier->name, $sTierTo);
		$aMetrics[] = [cChart::LABEL=>"Calls per min", cChart::METRIC=>$sMetric];
		$sMetric=cAppDynMetric::tierExtResponseTimes($oTier->name, $sTierTo);
		$aMetrics[] = [cChart::LABEL=>"Response Times in ms", cChart::METRIC=>$sMetric];
		$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>cRender::button_code("Go", cHttp::build_url($linkUrl, cRender::TO_TIER_QS, $sTierTo))];
	}
	cChart::metrics_table($oApp,$aMetrics,4,cRender::getRowClass(),null,cRender::CHART_WIDTH_LETTERBOX/3);

//################ CHART
cChart::do_footer();

cRender::html_footer();
?>