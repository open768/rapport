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


//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();

//####################################################################
cRender::html_header("Tiers in Application $oApp->name");
cRender::force_login();
cChart::do_header();
cRender::show_time_options( $oApp->name); 


//####################################################################
cRenderMenus::show_apps_menu("Application Tier Activity for:","tiers.php");
$sAppQS = cRender::get_base_app_QS();

cRender::appdButton(cAppDynControllerUI::application($oApp));
cRender::appdButton(cAppDynControllerUI::app_slow_transactions($oApp), "Slow Transactions");

//####################################################################
?>
<h2>Overall Activity in <?=$oApp->name?></h2>
<?php
$aMetrics = [];
$aMetrics[] = [cChart::LABEL=>"Overall Calls per min",cChart::METRIC=>cAppDynMetric::appCallsPerMin()];
$aMetrics[] = [cChart::LABEL=>"Overall response time in ms", cChart::METRIC=>cAppDynMetric::appResponseTimes()];
cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/2);			
?>
<p>
<!-- ************************************************** -->
<h2>Tiers Activity in application (<?=$oApp->name?>)</h2>
<?php
//-----------------------------------------------
$oResponse =cAppdyn::GET_Tiers($oApp);
foreach ( $oResponse as $oTier){
	$sTier=$oTier->name;
	if (cFilter::isTierFilteredOut($sTier)) continue;
	
	cRenderMenus::show_tier_functions($oTier);
	$aMetrics = [];
	$aMetrics[] = [cChart::LABEL=>"Calls per min",cChart::METRIC=>cAppDynMetric::tierCallsPerMin($sTier)];
	$aMetrics[] = [cChart::LABEL=>"Response time in ms", cChart::METRIC=>cAppDynMetric::tierResponseTimes($sTier)];
	cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/2);			
}
cChart::do_footer();

cRender::html_footer();
?>
