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
$gsAppQs = cRender::get_base_app_QS();
$oApp = cRenderObjs::get_current_app();
$aTiers =$oApp->GET_Tiers();

//####################################################################
cRender::html_header("$oApp->name Overview");
cRender::force_login();
cChart::$show_export_all = "0";
cChart::do_header();

$title ="$oApp->name&gt;Overview";
cRender::show_time_options( $title); 


//####################################################################
cRenderMenus::show_apps_menu("Show Overview for:","appoverview.php");
cRender::appdButton(cAppDynControllerUI::application($oApp));

//####################################################################
?>
<h2>Overview for <?=cRender::show_name(cRender::NAME_APP,$oApp)?></h2>
<ul>
	<li><a href="#app">Application Overview</a>
	<li><a href="#backend">Backends</a>
	<li><a href="#tperf">Tier Performance</a>
	<li><a href="#trans">Transactions</a>
</ul>

<h2><a name="app">Application Overview</a></h2>
<?php 
$aMetrics  = [
	[cChart::LABEL=>"response time in ms", cChart::METRIC=>cAppDynMetric::appResponseTimes()],
	[cChart::LABEL=>"Calls per min", cChart::METRIC=>cAppDynMetric::appCallsPerMin()],
	[cChart::LABEL=>"Slow Calls", cChart::METRIC=>cAppDynMetric::appSlowCalls()],
	[cChart::LABEL=>"Very Slow Calls", cChart::METRIC=>cAppDynMetric::appVerySlowCalls()],
	[cChart::LABEL=>"Stalled", cChart::METRIC=>cAppDynMetric::appStalledCount()],
	[cChart::LABEL=>"Errors per min", cChart::METRIC=>cAppDynMetric::appErrorsPerMin()],
	[cChart::LABEL=>"Exceptions", cChart::METRIC=>cAppDynMetric::appExceptionsPerMin()]
];
cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
cDebug::flush();

//####################################################################
?><h2><a name="tperf">Tier Performance</a></h2><?php
	//-----------------------------------------------
	$sClass=cRender::getRowClass();
	$iHeight=100;

	foreach ( $aTiers as $oTier){
		$sTier=$oTier->name;
		//------------------------------------------------
		?><hr><?php
		cRenderMenus::show_tier_functions($oTier);
		?><br><?php
		$aMetrics = [];
		$aMetrics[] = [cChart::LABEL=>"Calls Per min ", cChart::METRIC=>cAppDynMetric::tierCallsPerMin($sTier)];
		$aMetrics[] = [cChart::LABEL=>"Response Times", cChart::METRIC=>cAppDynMetric::tierResponseTimes($sTier)];
		$aMetrics[] = [cChart::LABEL=>"CPU Busy", cChart::METRIC=>cAppDynMetric::InfrastructureCpuBusy($sTier)];
		$aMetrics[] = [cChart::LABEL=>"Java Heap Used", cChart::METRIC=>cAppDynMetric::InfrastructureJavaHeapUsed($sTier)];
		$aMetrics[] = [cChart::LABEL=>".Net Heap Used", cChart::METRIC=>cAppDynMetric::InfrastructureDotnetHeapUsed($sTier)];
		cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
	}
?><p><?php
cDebug::flush();
	
//####################################################################
?><h2><a name="backend">Backends</a></h2><?php
	$oBackends =$oApp->GET_Backends();
	$sBackendURL = cHttp::build_url("backcalls.php",$gsAppQs );
	
	foreach ( $oBackends as $oBackend){
		$sClass=cRender::getRowClass();
			$aMetrics = [];
			$sMetricUrl=cAppDynMetric::backendCallsPerMin($oBackend->name);
			$aMetrics[] = [cChart::LABEL=>"Calls per min", cChart::METRIC=>$sMetricUrl];
			$sMetricUrl=cAppDynMetric::backendResponseTimes($oBackend->name);
			$aMetrics[] = [cChart::LABEL=>"Response Times", cChart::METRIC=>$sMetricUrl];
			$sMetricUrl=cAppDynMetric::backendErrorsPerMin($oBackend->name);
			$aMetrics[] = [cChart::LABEL=>"Errors Per min", cChart::METRIC=>$sMetricUrl];
			?><hr><?php
			cRender::button($oBackend->name, cHttp::build_url($sBackendURL, cRender::BACKEND_QS, $oBackend->name));
			?><br><?php
			cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
	}

//####################################################################
cDebug::flush();
?>
<h2><a name="trans">Transactions</a></h2>
<?php
//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("Transactions not support ed for Demo");
	cChart::do_footer();
	cRender::html_footer();
	exit;
}

$iHeight=100;
foreach ($aTiers as $oTier){
	$sTier = $oTier->name;
	$sTierQS = cHttp::build_QS($gsAppQs, cRender::TIER_QS, $sTier);
	$sTierQS = cHttp::build_QS($sTierQS, cRender::TIER_ID_QS, $oTier->id);
	
	?><h3><?=cRender::show_name(cRender::NAME_TIER,$oTier)?></h3><?php
	$aTransactions = cAppDyn::GET_tier_transaction_names($oTier);
	if ($aTransactions==null) {
		cRender::errorbox("unable to get transaction names");
		continue;
	}
		
		
	foreach ($aTransactions as $oTrans){
		$sTrans = $oTrans->name;
		$sClass=cRender::getRowClass();
		
		?><DIV class="<?=$sClass?>"><?php
			$sUrl = cHttp::build_url("../trans/transdetails.php?$sTierQS",cRender::TRANS_QS, $sTrans);
			cRender::button($oTrans->name, $sUrl);
			
			$aMetrics = [];
				$sMetricUrl=cAppdynMetric::transCallsPerMin($sTier, $sTrans);
				$aMetrics[] = [cChart::LABEL=>"Calls per min", cChart::METRIC=>$sMetricUrl];
				
				$sMetricUrl=cAppdynMetric::transResponseTimes($sTier, $sTrans);
				$aMetrics[] = [cChart::LABEL=>"Response times", cChart::METRIC=>$sMetricUrl];

				$sMetricUrl=cAppdynMetric::transErrors($sTier, $sTrans);
				$aMetrics[] = [cChart::LABEL=>"Error", cChart::METRIC=>$sMetricUrl];
				
				$sMetricUrl=cAppdynMetric::transCpuUsed($sTier, $sTrans);
				$aMetrics[] = [cChart::LABEL=>"CPU Used", cChart::METRIC=>$sMetricUrl];
				
			cChart::metrics_table($oApp, $aMetrics,4,$sClass);
		?></div><?php
	}
}

//####################################################################
cChart::do_footer();
cRender::html_footer();
?>
