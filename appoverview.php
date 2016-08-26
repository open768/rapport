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
	<script type="text/javascript" src="js/chart.php"></script>
<?php
cChart::do_header();
cChart::$width=cRender::CHART_WIDTH_LARGE/2;
cChart::$json_data_fn = "chart_getUrl";
cChart::$json_callback_fn = "chart_jsonCallBack";
cChart::$csv_url = "rest/getMetric.php";
cChart::$zoom_url = "metriczoom.php";
cChart::$save_fn = "save_fave_chart";

cChart::$compare_url = "compare.php";
cChart::$metric_qs = cRender::METRIC_QS;
cChart::$title_qs = cRender::TITLE_QS;
cChart::$app_qs = cRender::APP_QS;
cChart::$showZoom = false;
cChart::$showCSV = false;
cChart::$showSave = false;
cChart::$showCompare = false;

$title ="$gsApp&gt;Overview";
cRender::show_time_options( $title); 


//####################################################################
cRender::show_apps_menu("Show Overview for:","appoverview.php");
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

<?php

//####################################################################
$aMetrics  = [
	["response time in ms", cAppDynMetric::appResponseTimes()],
	["Calls per min", cAppDynMetric::appCallsPerMin()],
	["Slow Calls", cAppDynMetric::appSlowCalls()],
	["Very Slow Calls", cAppDynMetric::appVerySlowCalls()],
	["Stalled", cAppDynMetric::appStalledCount()],
	["Errors per min", cAppDynMetric::appErrorsPerMin()],
	["Exceptions", cAppDynMetric::appExceptionsPerMin()]
];

?>
<h2><a name="app">Application Overview</a></h2>
<?php 
$class=cRender::getRowClass();
cChart::$width=cRender::CHART_WIDTH_LARGE/3;
cRender::render_metrics_table($gsApp, $aMetrics,3, $class, null);

//####################################################################
?>
<h2><a name="tperf">Tier Performance</a></h2>
<?php
	//-----------------------------------------------
	$oResponse =cAppdyn::GET_Tiers($gsApp);
	$sClass=cRender::getRowClass();
	cChart::$width=cRender::CHART_WIDTH_LARGE/5;
	$iHeight=100;
?>
<table class="maintable">
	<tr>
		<th>Calls Per min</th>
		<th>Response Times</th>
		<th>CPU</th>
		<th>Java Heap</th>
		<th>.Net Heap</th>
	</tr>

	<?php
	foreach ( $oResponse as $oTier){
		$sTier=$oTier->name;
		
		//------------------------------------------------
		echo "<tr class='$sClass' align='left'>";
			echo "<th colspan='5'>";
				cRender::show_tier_functions($sTier, $oTier->id);
			echo "</th>";
		echo "</tr>";
		echo "<tr class='$sClass'>";
			echo "<td>";
				cChart::add("Calls Per min", cAppDynMetric::tierCallsPerMin($sTier), $gsApp, $iHeight);
			echo "</td>";
			echo "<td>";
				cChart::add("Response Times", cAppDynMetric::tierResponseTimes($sTier), $gsApp, $iHeight);
			echo "</td>";
			echo "<td>";
				cChart::add("CPU Busy", cAppDynMetric::InfrastructureCpuBusy($sTier), $gsApp, $iHeight);
			echo "</td>";
			echo "<td>";
				cChart::add("Java Heap Free", cAppDynMetric::InfrastructureJavaHeapFree($sTier), $gsApp, $iHeight);
			echo "</td>";
			echo "<td>";
				cChart::add(".Net Heap Used", cAppDynMetric::InfrastructureDotnetHeapUsed($sTier), $gsApp, $iHeight);
			echo "</td>";
		echo "</tr>";
	}
	?>
</table>

<?php
//####################################################################
	cChart::$width=cRender::CHART_WIDTH_LARGE/3;
	$oBackends =cAppdyn::GET_Backends($gsApp);
	$sBackendURL = cHttp::build_url("backcalls.php",$gsAppQs );
	$iHeight=150;
	$sClass=cRender::getRowClass();
?>
<h2><a name="backend">Backends</a></h2>
<table class="maintable">
	<tr>
		<th>Calls Per min</th>
		<th>Response Times</th>
		<th>Errors Per min</th>
	</tr>
	<?php
	foreach ( $oBackends as $oBackend){
		echo "<tr class='$sClass'>";
			echo "<th align='left' colspan='3'>";
				cRender::button($oBackend->name, cHttp::build_url($sBackendURL, cRender::BACKEND_QS, $oBackend->name));
			echo "</th>";
		echo "</tr>";
		echo "<tr  class='$sClass'>";
			echo "<td>";
				$sMetricUrl=cAppDynMetric::backendCallsPerMin($oBackend->name);
				cChart::add("Calls per min", $sMetricUrl, $gsApp, $iHeight);	
			echo "</td>";
			echo "<td>";
				$sMetricUrl=cAppDynMetric::backendResponseTimes($oBackend->name);
				cChart::add("Response Times", $sMetricUrl, $gsApp, $iHeight);	
			echo "</td>";
			echo "<td>";
				$sMetricUrl=cAppDynMetric::backendErrorsPerMin($oBackend->name);
				cChart::add("Errors Per min", $sMetricUrl, $gsApp, $iHeight);	
			echo "</td>";
		echo "</tr>";
	}
	?>
</table>
<?php
//####################################################################
?>
<h2><a name="trans">Transactions</a></h2>
<?php
$aTiers =cAppdyn::GET_Tiers($gsApp);
cChart::$width=cRender::CHART_WIDTH_LARGE/4;
$iHeight=100;

foreach ($aTiers as $oTier){
	$sTier = $oTier->name;
	$sTierQS = cHttp::build_QS($gsAppQs, cRender::TIER_QS, $sTier);
	$sTierQS = cHttp::build_QS($sTierQS, cRender::TIER_ID_QS, $oTier->id);
	
	$sClass=cRender::getRowClass();
	$aTransactions = cAppDyn::GET_tier_transaction_names($gsApp, $sTier);
	if ($aTransactions==null) continue;
	
	?>
	<h3><?=$sTier?></h3>
	<table class="maintable">
		<tr class="<?=$sClass?>">
			<th>Calls Per Min</th>
			<th>Response Times</th>
			<th>CPU Used</th>
			<th>Errors</th>
		</tr>
	<?php
		foreach ($aTransactions as $oTrans){
			$sTrans = $oTrans->name;
			?><tr class="<?=$sClass?>">
				<td colspan="4"><?php
					$sUrl = cHttp::build_url("transdetails.php?$sTierQS",cRender::TRANS_QS, $sTrans);
					cRender::button($oTrans->name, $sUrl);
				?></td>
			</tr><tr class="<?=$sClass?>">
				<td><?php
					$sMetricUrl=cAppdynMetric::transCallsPerMin($sTier, $sTrans);
					cChart::add("Calls per min", $sMetricUrl, $gsApp, $iHeight);
				?></td>
				<td><?php
					$sMetricUrl=cAppdynMetric::transResponseTimes($sTier, $sTrans);
					$sDivID = cChart::add("Response times", $sMetricUrl, $gsApp, $iHeight);
				?></td>
				<td><?php
					$sMetricUrl=cAppdynMetric::transErrors($sTier, $sTrans);
					$sDivID = cChart::add("Errors", $sMetricUrl, $gsApp, $iHeight);
				?></td>
				<td><?php
					$sMetricUrl=cAppdynMetric::transCpuUsed($sTier, $sTrans);
					$sDivID = cChart::add("CPU Used", $sMetricUrl, $gsApp, $iHeight);
				?></td>
			</tr><?php
		}
	
	?></table><?php
}



//####################################################################
	
	
	cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

	cRender::html_footer();
?>
