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

set_time_limit(200); // huge time limit as this takes a long time

//display the results
$oApp = cRender::get_current_app();
$oTier = cRender::get_current_tier();
$SHOW_PROGRESS=true;

//####################################################################
cRender::html_header("tier $oTier->name");
cRender::force_login();
cRender::show_time_options("$oApp->name&gt;$oTier->name"); 

cChart::do_header();

	//********************************************************************
	if (cAppdyn::is_demo()){
		cRender::errorbox("function not support ed for Demo");
		exit;
	}
	//********************************************************************

//####################################################################
$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null){
	cRenderMenus::show_tier_functions();
	cRenderMenus::show_tier_menu("Change Tier", "tier.php");
	cRenderMenus::show_tiernodes_menu("Tier infrastructure for..", "tierinfrstats.php");	
}
cRender::appdButton(cAppDynControllerUI::tier($oApp->id, $oTier->id));
cRender::appdButton(cAppDynControllerUI::tier_slow_transactions($oApp, $oTier),"Slow Transactions");

//####################################################################
?>
<h2>Activity</h2>
<?php
	$sClass = cRender::getRowClass();			
	$aMetrics = [];
	$sMetricUrl=cAppDynMetric::appCallsPerMin();
	$aMetrics[]= [cChart::LABEL=>"Overall Calls per min ($oApp->name) application", cChart::METRIC=>$sMetricUrl,cChart::STYLE=>cRender::getRowClass()];
	$sMetricUrl=cAppDynMetric::appResponseTimes();
	$aMetrics[]= [cChart::LABEL=>"Overall response time in ms ($oApp->name) application", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cAppDynMetric::tierCallsPerMin($oTier->name);
	$aMetrics[]= [cChart::LABEL=>"Calls per min for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,cChart::STYLE=>cRender::getRowClass()];
	$sMetricUrl=cAppDynMetric::tierResponseTimes($oTier->name);
	$aMetrics[]= [cChart::LABEL=>"Response times in ms for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl];
	cChart::metrics_table($oApp, $aMetrics, 2, $sClass);
?>
<h2>(<?=$oTier->name?>) Dashboard</h2>
<?php
	$aMetrics = [];
	$aMetrics[] = [cChart::LABEL=>"Slow Calls", cChart::METRIC=>cAppDynMetric::tierSlowCalls($oTier->name),cChart::STYLE=>cRender::getRowClass()];
	$aMetrics[] = [cChart::LABEL=>"Very Slow Calls", cChart::METRIC=>cAppDynMetric::tierVerySlowCalls($oTier->name)];
	$aMetrics[] = [cChart::LABEL=>"CPU Busy", cChart::METRIC=>cAppDynMetric::InfrastructureCpuBusy($oTier->name)];
	$aMetrics[] = [cChart::LABEL=>"Disk Free", cChart::METRIC=>cAppDynMetric::InfrastructureDiskFree($oTier->name),cChart::STYLE=>cRender::getRowClass()];
	$aMetrics[] = [cChart::LABEL=>"Errors Per Min", cChart::METRIC=>cAppDynMetric::tierErrorsPerMin($oTier->name)];
	$aMetrics[] = [cChart::LABEL=>"Exceptions Per Min", cChart::METRIC=>cAppDynMetric::tierExceptionsPerMin($oTier->name)];
	$aMetrics[] = [cChart::LABEL=>"Java Heap Used", cChart::METRIC=>cAppDynMetric::InfrastructureJavaHeapUsed($oTier->name),cChart::STYLE=>cRender::getRowClass()];
	$aMetrics[] = [cChart::LABEL=>".Net Heap used", cChart::METRIC=>cAppDynMetric::InfrastructureDotnetHeapUsed($oTier->name)];
	$aMetrics[] = [cChart::LABEL=>"Network In", cChart::METRIC=>cAppDynMetric::InfrastructureNetworkIncoming($oTier->name)];
	$aMetrics[] = [cChart::LABEL=>"Network Out", cChart::METRIC=>cAppDynMetric::InfrastructureNetworkOutgoing($oTier->name),cChart::STYLE=>cRender::getRowClass()];
	$aMetrics[] = [cChart::LABEL=>"Machine Availability", cChart::METRIC=>cAppDynMetric::InfrastructureMachineAvailability($oTier->name)];
	$aMetrics[] = [cChart::LABEL=>"Agent Availability", cChart::METRIC=>cAppDynMetric::InfrastructureAgentAvailability($oTier->name)];
	$sClass=cRender::getRowClass();
	cChart::metrics_table($oApp, $aMetrics, 3, $sClass);

//####################################################################
cChart::do_footer();
cRender::html_footer();
?>
