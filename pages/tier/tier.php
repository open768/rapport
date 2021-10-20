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
	$aMetrics = [];
	$sMetricUrl=cADMetric::appCallsPerMin();
	$aMetrics[]= [	cChart::LABEL=>"Overall Calls per min ($oApp->name) application", cChart::METRIC=>$sMetricUrl,cChart::STYLE=>cRender::getRowClass(),	];
	$sMetricUrl=cADMetric::appResponseTimes();
	$aMetrics[]= [cChart::LABEL=>"Overall response time in ms ($oApp->name) application", cChart::METRIC=>$sMetricUrl];
	cRenderCards::card_start("Activity for $oApp->name");
		cRenderCards::body_start();
			cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/2);
		cRenderCards::body_end();
		cRenderCards::action_start();
			cADCommon::button(cADControllerUI::tier($oApp, $oTier));
			cADCommon::button(cADControllerUI::tier_slow_transactions($oApp, $oTier),"Slow Transactions");
			cRender::button("Show Transactions", "../trans/apptrans.php?$sTierQS");
			if ($oCred->restricted_login == null){
				cRenderMenus::show_tier_functions();
				cDebug::flush();
				
				cRenderMenus::show_tier_menu("Change Tier", "tier.php");
				cDebug::flush();
			}
		cRenderCards::action_end();
	cRenderCards::card_end();

//####################################################################
?><h2>Activity for <?=cRender::show_name(cRender::NAME_TIER,$oTier)?></h2><?php
	$aMetrics = [];
	$sMetricUrl=cADMetric::tierCallsPerMin($oTier->name);	
	$sQs = cHttp::build_qs($sTierQS, cRender::METRIC_TYPE_QS, cADMetric::METRIC_TYPE_ACTIVITY);
	$aMetrics[]= [
		cChart::LABEL=>"Calls per min for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,cChart::STYLE=>cRender::getRowClass(),
		cChart::GO_HINT=>"All Nodes",	cChart::GO_URL=>"tierallnodeinfra.php?$sQs"
	];
	
	$sQs = cHttp::build_qs($sTierQS, cRender::METRIC_TYPE_QS, cADMetric::METRIC_TYPE_RESPONSE_TIMES);
	$sMetricUrl=cADMetric::tierResponseTimes($oTier->name);
	$aMetrics[]= [
		cChart::LABEL=>"Response times in ms for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_HINT=>"All Nodes",	cChart::GO_URL=>"tierallnodeinfra.php?$sQs"
	];
	cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/2);
	cDebug::flush();
?>
<h2>Key Metrics for <?=cRender::show_name(cRender::NAME_TIER,$oTier)?></h2>
<?php
	//####################################################################
	$aMetrics = [];
	$aMetrics[] = [cChart::LABEL=>"Slow Calls", cChart::METRIC=>cADMetric::tierSlowCalls($oTier->name),cChart::STYLE=>cRender::getRowClass()];
	$aMetrics[] = [cChart::LABEL=>"Very Slow Calls", cChart::METRIC=>cADMetric::tierVerySlowCalls($oTier->name)];

	$sQs = cHttp::build_qs($sTierQS, cRender::METRIC_TYPE_QS, cADInfraMetric::METRIC_TYPE_INFR_CPU_BUSY);
	$aMetrics[] = [
		cChart::LABEL=>"CPU Busy", cChart::METRIC=>cADMetric::InfrastructureCpuBusy($oTier->name), cChart::HIDEIFNODATA=>1,
		cChart::GO_HINT=>"All Nodes",	cChart::GO_URL=>"tierallnodeinfra.php?$sQs"
	];
	
	$aMetrics[] = [
		cChart::LABEL=>"Disk Free", cChart::METRIC=>cADMetric::InfrastructureDiskFree($oTier->name),cChart::STYLE=>cRender::getRowClass(), cChart::HIDEIFNODATA=>1
	];
	
	$sQs = cHttp::build_qs($sTierQS, cRender::METRIC_TYPE_QS, cADMetric::METRIC_TYPE_ERRORS);
	$aMetrics[] = [
		cChart::LABEL=>"Errors Per Min", cChart::METRIC=>cADMetric::tierErrorsPerMin($oTier->name), cChart::HIDEIFNODATA=>1,
		cChart::GO_HINT=>"All Nodes",	cChart::GO_URL=>"tierallnodeinfra.php?$sQs"
	];
	
	$aMetrics[] = [
		cChart::LABEL=>"Exceptions Per Min", cChart::METRIC=>cADMetric::tierExceptionsPerMin($oTier->name), cChart::HIDEIFNODATA=>1
	];
	
	$sQs = cHttp::build_qs($sTierQS, cRender::METRIC_TYPE_QS, cADInfraMetric::METRIC_TYPE_INFR_JAVA_HEAP_USED);
	$aMetrics[] = [
		cChart::LABEL=>"Java Heap Used", cChart::METRIC=>cADMetric::InfrastructureJavaHeapUsed($oTier->name),cChart::STYLE=>cRender::getRowClass(), cChart::HIDEIFNODATA=>1,
		cChart::GO_HINT=>"All Nodes",	cChart::GO_URL=>"tierallnodeinfra.php?$sQs"
	];
	$aMetrics[] = [
		cChart::LABEL=>".Net Heap used", cChart::METRIC=>cADMetric::InfrastructureDotnetHeapUsed($oTier->name), cChart::HIDEIFNODATA=>1
	];
	$aMetrics[] = [
		cChart::LABEL=>"Network In", cChart::METRIC=>cADMetric::InfrastructureNetworkIncoming($oTier->name), cChart::HIDEIFNODATA=>1
	];
	$aMetrics[] = [
		cChart::LABEL=>"Network Out", cChart::METRIC=>cADMetric::InfrastructureNetworkOutgoing($oTier->name),cChart::STYLE=>cRender::getRowClass(), cChart::HIDEIFNODATA=>1
	];
	$aMetrics[] = [
		cChart::LABEL=>"Machine Availability", cChart::METRIC=>cADMetric::InfrastructureMachineAvailability($oTier->name), cChart::HIDEIFNODATA=>1
	];
	$aMetrics[] = [
		cChart::LABEL=>"Agent Availability", cChart::METRIC=>cADMetric::InfrastructureAgentAvailability($oTier->name), cChart::HIDEIFNODATA=>1
	];
		
	cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);

//####################################################################
cChart::do_footer();
cRenderHtml::footer();
?>
