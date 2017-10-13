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


//-----------------------------------------------
$gsApp = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$gsAppQs = cRender::get_base_app_QS();

//####################################################################
cRender::html_header("$gsApp Overview");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::$show_export_all = false;
cChart::do_header();


$title ="$gsApp&gt;Overview";
cRender::show_time_options( $title); 


//####################################################################
cRenderMenus::show_apps_menu("Show Overview for:","appoverview.php");
$gsAppQs = cRender::get_base_app_QS();
cRender::appdButton(cAppDynControllerUI::application($aid));

//####################################################################
?>
<h2>Overview for <?=$gsApp?></h2>
<ul>
	<li><a href="#app">Application Overview</a>
	<li><a href="#backend">Backends</a>
	<li><a href="#tperf">Tier Performance</a>
	<li><a href="#trans">Transactions</a>
</ul>

<h2><a name="app">Application Overview</a></h2>
<?php 
$aMetrics  = [
	["response time in ms", cAppDynMetric::appResponseTimes()],
	["Calls per min", cAppDynMetric::appCallsPerMin()],
	["Slow Calls", cAppDynMetric::appSlowCalls()],
	["Very Slow Calls", cAppDynMetric::appVerySlowCalls()],
	["Stalled", cAppDynMetric::appStalledCount()],
	["Errors per min", cAppDynMetric::appErrorsPerMin()],
	["Exceptions", cAppDynMetric::appExceptionsPerMin()]
];
$class=cRender::getRowClass();
cRender::render_metrics_table($gsApp, $aMetrics,3, $class, null);

//####################################################################
?><h2><a name="tperf">Tier Performance</a></h2><?php
	//-----------------------------------------------
	$oResponse =cAppdyn::GET_Tiers($gsApp);
	$sClass=cRender::getRowClass();
	$iHeight=100;

	foreach ( $oResponse as $oTier){
		$sTier=$oTier->name;
		$sClass = cRender::getRowClass();
		?><div class="<?=$sClass?>"><?php
			//------------------------------------------------
			cRenderMenus::show_tier_functions($sTier, $oTier->id);
			$aMetrics = [];
			$aMetrics[] = ["Calls Per min ", cAppDynMetric::tierCallsPerMin($sTier)];
			$aMetrics[] = ["Response Times", cAppDynMetric::tierResponseTimes($sTier)];
			$aMetrics[] = ["CPU Busy", cAppDynMetric::InfrastructureCpuBusy($sTier)];
			$aMetrics[] = ["Java Heap Used", cAppDynMetric::InfrastructureJavaHeapUsed($sTier)];
			$aMetrics[] = [".Net Heap Used", cAppDynMetric::InfrastructureDotnetHeapUsed($sTier)];
			cRender::render_metrics_table($gsApp, $aMetrics,3,$sClass);
		?></div><?php
	}
	
//####################################################################
?><h2><a name="backend">Backends</a></h2><?php
	$oBackends =cAppdyn::GET_Backends($gsApp);
	$sBackendURL = cHttp::build_url("backcalls.php",$gsAppQs );
	
	foreach ( $oBackends as $oBackend){
		$sClass=cRender::getRowClass();
		?><div class="<?=$sClass?>"><?php
			cRender::button($oBackend->name, cHttp::build_url($sBackendURL, cRender::BACKEND_QS, $oBackend->name));
			$aMetrics = [];
			$sMetricUrl=cAppDynMetric::backendCallsPerMin($oBackend->name);
			$aMetrics[] = ["Calls per min", $sMetricUrl];
			$sMetricUrl=cAppDynMetric::backendResponseTimes($oBackend->name);
			$aMetrics[] = ["Response Times", $sMetricUrl];
			$sMetricUrl=cAppDynMetric::backendErrorsPerMin($oBackend->name);
			$aMetrics[] = ["Errors Per min", $sMetricUrl];
			cRender::render_metrics_table($gsApp, $aMetrics,3,$sClass);
		?></div><?php
	}

//####################################################################
?>
<h2><a name="trans">Transactions</a></h2>
<?php
$aTiers =cAppdyn::GET_Tiers($gsApp);
$iHeight=100;

foreach ($aTiers as $oTier){
	$sTier = $oTier->name;
	$sTierQS = cHttp::build_QS($gsAppQs, cRender::TIER_QS, $sTier);
	$sTierQS = cHttp::build_QS($sTierQS, cRender::TIER_ID_QS, $oTier->id);
	
	$aTransactions = cAppDyn::GET_tier_transaction_names($gsApp, $sTier);
	if ($aTransactions==null) continue;
	
	?>
	<h3><?=$sTier?></h3>
	<?php
		foreach ($aTransactions as $oTrans){
			$sTrans = $oTrans->name;
			$sClass=cRender::getRowClass();
			
			?><DIV class="<?=$sClass?>"><?php
				$sUrl = cHttp::build_url("transdetails.php?$sTierQS",cRender::TRANS_QS, $sTrans);
				cRender::button($oTrans->name, $sUrl);
				
				$aMetrics = [];
					$sMetricUrl=cAppdynMetric::transCallsPerMin($sTier, $sTrans);
					$aMetrics[] = ["Calls per min", $sMetricUrl];
					
					$sMetricUrl=cAppdynMetric::transResponseTimes($sTier, $sTrans);
					$aMetrics[] = ["Response times", $sMetricUrl];

					$sMetricUrl=cAppdynMetric::transErrors($sTier, $sTrans);
					$aMetrics[] = ["Error", $sMetricUrl];
					
					$sMetricUrl=cAppdynMetric::transCpuUsed($sTier, $sTrans);
					$aMetrics[] = ["CPU Used", $sMetricUrl];
					
				cRender::render_metrics_table($gsApp, $aMetrics,4,$sClass);
			?></div><?php
		}
	
	?></table><?php
}



//####################################################################
	
	
	cChart::do_footer();

	cRender::html_footer();
?>
