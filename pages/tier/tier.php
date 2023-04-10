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
//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";


set_time_limit(200); // huge time limit as this takes a long time

//display the results
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$sTierQS = cRenderQS::get_base_tier_QS($oTier);

//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("tier $oTier->name");
cRender::force_login();

cChart::do_header();

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

//####################################################################
$oCred = cRenderObjs::get_AD_credentials();

//####################################################################
?><br><?php
cDebug::flush();

//####################################################################
//overall stats
	$aMetrics = [];
	$sMetricUrl=cADAppMetricPaths::appCallsPerMin();
	$aMetrics[]= [	cChart::LABEL=>"Overall Calls per min ($oApp->name) application", cChart::METRIC=>$sMetricUrl,cChart::STYLE=>cRender::getRowClass(),	];
	$sMetricUrl=cADAppMetricPaths::appResponseTimes();
	$aMetrics[]= [cChart::LABEL=>"Overall response time in ms ($oApp->name) application", cChart::METRIC=>$sMetricUrl];
	cRenderCards::card_start("Activity for $oApp->name");
		cRenderCards::body_start();
			cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
		cRenderCards::body_end();
		cRenderCards::action_start();
			cADCommon::button(cADControllerUI::tier($oApp, $oTier));
			cADCommon::button(cADControllerUI::tier_slow_transactions($oApp, $oTier),"Slow Transactions");
			cRender::button("Show Transactions", "../trans/apptrans.php?$sTierQS");
			if ($oCred->restricted_login == null){
				cRenderMenus::show_tier_functions();
				cDebug::flush();
				
				cRenderMenus::show_tier_menu("Change Tier", cCommon::filename());
				cDebug::flush();
			}
		cRenderCards::action_end();
	cRenderCards::card_end();

//####################################################################
//Tier Statistics
	$aMetrics = [];
	$sMetricUrl=cADTierMetricPaths::tierCallsPerMin($oTier->name);	
	$sQs = cHttp::build_qs($sTierQS, cRenderQS::METRIC_TYPE_QS, cADMetricPaths::METRIC_TYPE_ACTIVITY);
	$aMetrics[]= [
		cChart::LABEL=>"Calls per min for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,cChart::STYLE=>cRender::getRowClass(),
		cChart::GO_HINT=>"All Nodes",	cChart::GO_URL=>"tierallnodeinfra.php?$sQs"
	];
	
	$sQs = cHttp::build_qs($sTierQS, cRenderQS::METRIC_TYPE_QS, cADMetricPaths::METRIC_TYPE_RESPONSE_TIMES);
	$sMetricUrl=cADTierMetricPaths::tierResponseTimes($oTier->name);
	$aMetrics[]= [
		cChart::LABEL=>"Response times in ms for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_HINT=>"All Nodes",	cChart::GO_URL=>"tierallnodeinfra.php?$sQs"
	];
	cRenderCards::card_start("Activity for $oTier->name");
		cRenderCards::body_start();
			cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
		cRenderCards::body_end();
	cRenderCards::card_end();

//####################################################################
//Tier Statistics
	$aMetrics = [];
	$aMetrics[] = [cChart::LABEL=>"Slow Calls", cChart::METRIC=>cADTierMetricPaths::tierSlowCalls($oTier->name),cChart::STYLE=>cRender::getRowClass()];
	$aMetrics[] = [cChart::LABEL=>"Very Slow Calls", cChart::METRIC=>cADTierMetricPaths::tierVerySlowCalls($oTier->name)];

	$sQs = cHttp::build_qs($sTierQS, cRenderQS::METRIC_TYPE_QS, cADInfraMetric::METRIC_TYPE_INFR_CPU_BUSY);
	$aMetrics[] = [
		cChart::LABEL=>"CPU Busy", cChart::METRIC=>cADInfraMetric::InfrastructureCpuBusy($oTier->name), cChart::HIDEIFNODATA=>1,
		cChart::GO_HINT=>"All Nodes",	cChart::GO_URL=>"tierallnodeinfra.php?$sQs"
	];
	
	$aMetrics[] = [
		cChart::LABEL=>"Disk Free", cChart::METRIC=>cADInfraMetric::InfrastructureDiskFree($oTier->name),cChart::STYLE=>cRender::getRowClass(), cChart::HIDEIFNODATA=>1
	];
	
	$sQs = cHttp::build_qs($sTierQS, cRenderQS::METRIC_TYPE_QS, cADMetricPaths::METRIC_TYPE_ERRORS);
	$aMetrics[] = [
		cChart::LABEL=>"Errors Per Min", cChart::METRIC=>cADTierMetricPaths::tierErrorsPerMin($oTier->name), cChart::HIDEIFNODATA=>1,
		cChart::GO_HINT=>"All Nodes",	cChart::GO_URL=>"tierallnodeinfra.php?$sQs"
	];
	
	$aMetrics[] = [
		cChart::LABEL=>"Exceptions Per Min", cChart::METRIC=>cADTierMetricPaths::tierExceptionsPerMin($oTier->name), cChart::HIDEIFNODATA=>1
	];
	
	$sQs = cHttp::build_qs($sTierQS, cRenderQS::METRIC_TYPE_QS, cADInfraMetric::METRIC_TYPE_INFR_JAVA_HEAP_USED);
	$aMetrics[] = [
		cChart::LABEL=>"Java Heap Used", cChart::METRIC=>cADInfraMetric::InfrastructureJavaHeapUsed($oTier->name),cChart::STYLE=>cRender::getRowClass(), cChart::HIDEIFNODATA=>1,
		cChart::GO_HINT=>"All Nodes",	cChart::GO_URL=>"tierallnodeinfra.php?$sQs"
	];
	$aMetrics[] = [
		cChart::LABEL=>".Net Heap used", cChart::METRIC=>cADInfraMetric::InfrastructureDotnetHeapUsed($oTier->name), cChart::HIDEIFNODATA=>1
	];
	$aMetrics[] = [
		cChart::LABEL=>"Network In", cChart::METRIC=>cADInfraMetric::InfrastructureNetworkIncoming($oTier->name), cChart::HIDEIFNODATA=>1
	];
	$aMetrics[] = [
		cChart::LABEL=>"Network Out", cChart::METRIC=>cADInfraMetric::InfrastructureNetworkOutgoing($oTier->name),cChart::STYLE=>cRender::getRowClass(), cChart::HIDEIFNODATA=>1
	];
	$aMetrics[] = [
		cChart::LABEL=>"Machine Availability", cChart::METRIC=>cADInfraMetric::InfrastructureMachineAvailability($oTier->name), cChart::HIDEIFNODATA=>1
	];
	$aMetrics[] = [
		cChart::LABEL=>"Agent Availability", cChart::METRIC=>cADInfraMetric::InfrastructureAgentAvailability($oTier->name), cChart::HIDEIFNODATA=>1
	];
		
	cRenderCards::card_start("Key Metrics for $oTier->name");
		cRenderCards::body_start();
			cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
		cRenderCards::body_end();
	cRenderCards::card_end();

//####################################################################
cChart::do_footer();
cRenderHtml::footer();
?>
