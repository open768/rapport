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
$app = cHeader::get(cRender::APP_QS);
$tier = cHeader::get(cRender::TIER_QS);

$SHOW_PROGRESS=true;

$sAppQs = cRender::get_base_app_QS();
$sTierQs = cRender::get_base_tier_QS();

//####################################################################
cRender::html_header("tier $tier");
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cRender::force_login();
cRender::show_time_options("$app&gt;$tier"); 

cChart::do_header();

//####################################################################
$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null){
	$sInfraUrl = cHttp::build_url("tierinfrstats.php",$sTierQs);

	cRenderMenus::show_tier_functions();
	cRenderMenus::show_tier_menu("Change Tier", "tier.php");
	cRenderMenus::show_tiernodes_menu("Tier infrastructure for..", "tierinfrstats.php");	
}

//####################################################################
?>
<h2>Activity</h2>
<?php
	$sClass = cRender::getRowClass();			
	$aMetrics = [];
	$sMetricUrl=cAppDynMetric::appCallsPerMin();
	$aMetrics[]= [cChart::LABEL=>"Overall Calls per min ($app) application", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cAppDynMetric::appResponseTimes();
	$aMetrics[]= [cChart::LABEL=>"Overall response time in ms ($app) application", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cAppDynMetric::tierCallsPerMin($tier);
	$aMetrics[]= [cChart::LABEL=>"Calls per min for ($tier) tier", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cAppDynMetric::tierResponseTimes($tier);
	$aMetrics[]= [cChart::LABEL=>"Response times in ms for ($tier) tier", cChart::METRIC=>$sMetricUrl];
	cChart::metrics_table($app, $aMetrics, 2, $sClass);
?>
<h2>(<?=$tier?>) Dashboard</h2>
<?php
	$aMetrics = [];
	$aMetrics[] = [cChart::LABEL=>"Slow Calls", cChart::METRIC=>cAppDynMetric::tierSlowCalls($tier)];
	$aMetrics[] = [cChart::LABEL=>"Very Slow Calls", cChart::METRIC=>cAppDynMetric::tierVerySlowCalls($tier)];
	$aMetrics[] = [cChart::LABEL=>"CPU Busy", cChart::METRIC=>cAppDynMetric::InfrastructureCpuBusy($tier)];
	$aMetrics[] = [cChart::LABEL=>"Disk Free", cChart::METRIC=>cAppDynMetric::InfrastructureDiskFree($tier)];
	$aMetrics[] = [cChart::LABEL=>"Errors Per Min", cChart::METRIC=>cAppDynMetric::tierErrorsPerMin($tier)];
	$aMetrics[] = [cChart::LABEL=>"Exceptions Per Min", cChart::METRIC=>cAppDynMetric::tierExceptionsPerMin($tier)];
	$aMetrics[] = [cChart::LABEL=>"Java Heap Used", cChart::METRIC=>cAppDynMetric::InfrastructureJavaHeapUsed($tier)];
	$aMetrics[] = [cChart::LABEL=>".Net Heap used", cChart::METRIC=>cAppDynMetric::InfrastructureDotnetHeapUsed($tier)];
	$aMetrics[] = [cChart::LABEL=>"Network In", cChart::METRIC=>cAppDynMetric::InfrastructureNetworkIncoming($tier)];
	$aMetrics[] = [cChart::LABEL=>"Network Out", cChart::METRIC=>cAppDynMetric::InfrastructureNetworkOutgoing($tier)];
	$aMetrics[] = [cChart::LABEL=>"Machine Availability", cChart::METRIC=>cAppDynMetric::InfrastructureMachineAvailability($tier)];
	$aMetrics[] = [cChart::LABEL=>"Agent Availability", cChart::METRIC=>cAppDynMetric::InfrastructureAgentAvailability($tier)];
	$sClass=cRender::getRowClass();
	cChart::metrics_table($app, $aMetrics, 3, $sClass);

//####################################################################
cChart::do_footer();
cRender::html_footer();
?>
