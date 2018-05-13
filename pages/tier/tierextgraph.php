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


//####################################################################
set_time_limit(200); // huge time limit as this takes a long time

//display the results
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;

$gsTierQs = cRender::get_base_tier_QS();

//################### CHART HEADER ########################################
cRender::html_header("External tier calls");
cRender::force_login();
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE/2;

//###################### DATA ########################################
$title = "$oApp->name&gt;$oTier->name&gt;External Calls";

cRender::show_time_options($title); 
//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************


$oCred = cRenderObjs::get_appd_credentials();
if ($oCred->restricted_login == null){
	cRenderMenus::show_app_functions();
	cRenderMenus::show_tier_functions();
	cRenderMenus::show_tier_menu("Change Tier to", "tierextgraph.php");
}
cRender::button("show as table", "tierextcalls.php?$gsTierQs");
cRender::appdButton(cAppDynControllerUI::tier_slow_remote($oApp, $oTier),"Slow Remote Calls");

//************* basic information about the tier *********************
?>
<h2>External calls made from <?=cRender::show_name(cRender::NAME_TIER,$oTier)?> tier</h2>
<h3>Overall Stats for tier</h3>
<?php
	
	$aMetrics=[];
	$sMetricUrl=cAppDynMetric::tierCallsPerMin($oTier->name);
	$aMetrics[] = [cChart::LABEL=>"Overall Calls per min for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cAppDynMetric::tierResponseTimes($oTier->name);
	$aMetrics[] = [cChart::LABEL=>"Overall  response times in ms for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cAppDynMetric::tierErrorsPerMin($oTier->name);
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
		$sMetric=cAppDynMetric::tierExtCallsPerMin($oTier->name, $sTierTo);
		$aMetrics[] = [cChart::LABEL=>"Calls per min", cChart::METRIC=>$sMetric, cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"Drill down"];
		$sMetric=cAppDynMetric::tierExtResponseTimes($oTier->name, $sTierTo);
		$aMetrics[] = [cChart::LABEL=>"Response Times in ms", cChart::METRIC=>$sMetric];
		$sMetric=cAppDynMetric::tierExtErrorsPerMin($oTier->name, $sTierTo);
		$aMetrics[] = [cChart::LABEL=>"Errors Per minuts", cChart::METRIC=>$sMetric];
	}
	cChart::metrics_table($oApp,$aMetrics,4,cRender::getRowClass());

//################ CHART
cChart::do_footer();

cRender::html_footer();
?>