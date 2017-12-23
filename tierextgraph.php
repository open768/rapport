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
$app = cHeader::get(cRender::APP_QS);
$tier = cHeader::get(cRender::TIER_QS);
$tid = cHeader::get(cRender::TIER_ID_QS);

$gsAppQs = cRender::get_base_app_QS();
$gsTierQs = cRender::get_base_tier_QS();

//################### CHART HEADER ########################################
cRender::html_header("External tier calls");
cRender::force_login();
cChart::do_header();
cChart::$width=cRender::CHART_WIDTH_LARGE/2;

//###################### DATA ########################################
$title = "$app&gt;$tier&gt;External Calls";
$tierQS = cRender::get_base_tier_QS();

cRender::show_time_options($title); 
$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null){
	cRenderMenus::show_app_functions();
	cRenderMenus::show_tier_functions();
	cRenderMenus::show_tier_menu("Change Tier to", "tierextgraph.php");
}

//************* basic information about the tier *********************
?>
<h2>External calls made from (<?=$tier?>) tier</h2>
<h3>Overall Stats for tier</h3>
<?php
	$aMetrics=[];
	$sMetricUrl=cAppDynMetric::tierCallsPerMin($tier);
	$aMetrics[] = [cChart::LABEL=>"Overall Calls per min for ($tier) tier", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cAppDynMetric::tierResponseTimes($tier);
	$aMetrics[] = [cChart::LABEL=>"Overall  response times in ms for ($tier) tier", cChart::METRIC=>$sMetricUrl];
	cChart::metrics_table($app,$aMetrics,2,cRender::getRowClass());


	$oResponse = cAppdyn::GET_tier_ExtCalls_Metric_heirarchy($app, $tier);
	$linkUrl = cHttp::build_url("tiertotier.php", $gsAppQs);
	$linkUrl = cHttp::build_url($linkUrl, cRender::FROM_TIER_QS, $tier);
	$linkUrl = cHttp::build_url($linkUrl, cRender::TIER_ID_QS, $tid);
?><h3>External calls</h3><?php
	$aMetrics=[];
	
	foreach ($oResponse as $oDetail){
		$sTierTo = $oDetail->name;
		$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>$sTierTo];
		$sMetric=cAppDynMetric::tierExtCallsPerMin($tier, $sTierTo);
		$aMetrics[] = [cChart::LABEL=>"Calls per min", cChart::METRIC=>$sMetric];
		$sMetric=cAppDynMetric::tierExtResponseTimes($tier, $sTierTo);
		$aMetrics[] = [cChart::LABEL=>"Response Times in ms", cChart::METRIC=>$sMetric];
		$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>cRender::button_code("Go", cHttp::build_url($linkUrl, cRender::TO_TIER_QS, $sTierTo))];
	}
	cChart::metrics_table($app,$aMetrics,4,cRender::getRowClass(),null,cRender::CHART_WIDTH_LETTERBOX/3);

//################ CHART
cChart::do_footer();

cRender::html_footer();
?>