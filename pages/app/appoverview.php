<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";



//-----------------------------------------------
$gsAppQs = cRenderQS::get_base_app_QS($oApp);
$oApp = cRenderObjs::get_current_app();
$aTiers =$oApp->GET_Tiers();

//####################################################################
cRenderHtml::header("$oApp->name Overview");
cRender::force_login();
cChart::$show_export_all = "0";
cChart::do_header();

$title ="$oApp->name&gt;Overview";

//####################################################################
cRenderMenus::show_apps_menu("Show Overview for:");
cADCommon::button(cADControllerUI::application($oApp));

//####################################################################
?>
<h2>Overview for <?=$oApp->name?></h2>
<ul>
	<li><a href="#app">Application Overview</a>
	<li><a href="#backend">Backends</a>
	<li><a href="#tperf">Tier Performance</a>
	<li><a href="#trans">Transactions</a>
</ul>

<h2><a name="app">Application Overview</a></h2>
<?php 
$aMetrics  = [
	[cChart::LABEL=>"response time in ms", cChart::METRIC=>cADMetricPaths::appResponseTimes()],
	[cChart::LABEL=>"Calls per min", cChart::METRIC=>cADMetricPaths::appCallsPerMin()],
	[cChart::LABEL=>"Slow Calls", cChart::METRIC=>cADMetricPaths::appSlowCalls()],
	[cChart::LABEL=>"Very Slow Calls", cChart::METRIC=>cADMetricPaths::appVerySlowCalls()],
	[cChart::LABEL=>"Stalled", cChart::METRIC=>cADMetricPaths::appStalledCount()],
	[cChart::LABEL=>"Errors per min", cChart::METRIC=>cADMetricPaths::appErrorsPerMin()],
	[cChart::LABEL=>"Exceptions", cChart::METRIC=>cADMetricPaths::appExceptionsPerMin()]
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
		$aMetrics[] = [cChart::LABEL=>"Calls Per min ", cChart::METRIC=>cADMetricPaths::tierCallsPerMin($sTier)];
		$aMetrics[] = [cChart::LABEL=>"Response Times", cChart::METRIC=>cADMetricPaths::tierResponseTimes($sTier)];
		$aMetrics[] = [cChart::LABEL=>"CPU Busy", cChart::METRIC=>cADMetricPaths::InfrastructureCpuBusy($sTier)];
		$aMetrics[] = [cChart::LABEL=>"Java Heap Used", cChart::METRIC=>cADMetricPaths::InfrastructureJavaHeapUsed($sTier)];
		$aMetrics[] = [cChart::LABEL=>".Net Heap Used", cChart::METRIC=>cADMetricPaths::InfrastructureDotnetHeapUsed($sTier)];
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
			$sMetricUrl=cADMetricPaths::backendCallsPerMin($oBackend->name);
			$aMetrics[] = [cChart::LABEL=>"Calls per min", cChart::METRIC=>$sMetricUrl];
			$sMetricUrl=cADMetricPaths::backendResponseTimes($oBackend->name);
			$aMetrics[] = [cChart::LABEL=>"Response Times", cChart::METRIC=>$sMetricUrl];
			$sMetricUrl=cADMetricPaths::backendErrorsPerMin($oBackend->name);
			$aMetrics[] = [cChart::LABEL=>"Errors Per min", cChart::METRIC=>$sMetricUrl];
			?><hr><?php
			cRender::button($oBackend->name, cHttp::build_url($sBackendURL, cRenderQS::BACKEND_QS, $oBackend->name));
			?><br><?php
			cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
	}

//####################################################################
cDebug::flush();
?>
<h2><a name="trans">Transactions</a></h2>
<?php
//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("Transactions not supported for Demo");
	cChart::do_footer();
	cRenderHtml::footer();
	exit;
}

$iHeight=100;
foreach ($aTiers as $oTier){
	$sTier = $oTier->name;
	$sTierQS = cHttp::build_QS($gsAppQs, cRenderQS::TIER_QS, $sTier);
	$sTierQS = cHttp::build_QS($sTierQS, cRenderQS::TIER_ID_QS, $oTier->id);
	
	?><h3><?=$oTier->name?></h3><?php
	$aTransactions = $oTier->GET_all_transaction_names();
	if ($aTransactions==null) {
		cCommon::errorbox("unable to get transaction names");
		continue;
	}
		
		
	foreach ($aTransactions as $oTrans){
		$sTrans = $oTrans->name;
		$sClass=cRender::getRowClass();
		
		?><DIV class="<?=$sClass?>"><?php
			$sUrl = cHttp::build_url("../trans/transdetails.php?$sTierQS",cRenderQS::TRANS_QS, $sTrans);
			cRender::button($oTrans->name, $sUrl);
			
			$aMetrics = [];
				$sMetricUrl=cADMetricPaths::transCallsPerMin($oTrans);
				$aMetrics[] = [cChart::LABEL=>"Calls per min", cChart::METRIC=>$sMetricUrl];
				
				$sMetricUrl=cADMetricPaths::transResponseTimes($oTrans);
				$aMetrics[] = [cChart::LABEL=>"Response times", cChart::METRIC=>$sMetricUrl];

				$sMetricUrl=cADMetricPaths::transErrors($oTrans);
				$aMetrics[] = [cChart::LABEL=>"Error", cChart::METRIC=>$sMetricUrl];
				
				$sMetricUrl=cADMetricPaths::transCpuUsed($oTrans);
				$aMetrics[] = [cChart::LABEL=>"CPU Used", cChart::METRIC=>$sMetricUrl];
				
			cChart::metrics_table($oApp, $aMetrics,4,$sClass);
		?></div><?php
	}
}

//####################################################################
cChart::do_footer();
cRenderHtml::footer();
?>
