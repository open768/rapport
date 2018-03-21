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
cRender::html_header("Infrastructure");
cRender::force_login();
cChart::do_header();

//####################################################################
//####################################################################
$sTitle ="Infrastructure Overview for $oApp->name";
cRender::show_time_options( $sTitle); 
$oApp = cRender::get_current_app();

?>
<h2><?=$sTitle?></h2>
<?php
//####################################################################
cRenderMenus::show_apps_menu("Infrastructure","appinfra.php");
?>
<h2>Tiers</h2>
<?php
$aActivityMetrics = [cRender::METRIC_TYPE_ACTIVITY, cRender::METRIC_TYPE_RESPONSE_TIMES];
$aMetricTypes = cAppDynInfraMetric::getInfrastructureMetricTypes();

$aTiers =cAppdyn::GET_Tiers($oApp);
foreach ($aTiers as $oTier){
	if (cFilter::isTierFilteredOut($oTier->name)) continue;
	
	cRenderMenus::show_tier_functions($oTier->name, $oTier->id);
	$sTierQs = cRender::build_tier_qs($oApp,$oTier );
	$sAllUrl = cHttp::build_url("../tier/tierallnodeinfra.php", $sTierQs);

	$aMetrics = [];
	foreach ($aMetricTypes as $sMetricType){
		$oMetric = cAppDynInfraMetric::getInfrastructureMetric($oTier->name,null,$sMetricType);
		$sUrl = cHttp::build_url($sAllUrl, cRender::METRIC_TYPE_QS, $sMetricType);
		$aMetrics[] = [cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric, cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See $oMetric->caption for all servers"];
	}
	
	$sClass = cRender::getRowClass();
	cChart::render_metrics($oApp,$aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
}

//####################################################################
//####################################################################
cChart::do_footer();
cRender::html_footer();
?>
