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
require_once "$root/inc/inc-charts.php";


set_time_limit(200); // huge time limit as this takes a long time

//display the results
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$sTierQS = cRenderQS::get_base_tier_QS($oTier);

//####################################################################
cRenderHtml::header("tier $oTier->name");
cRender::force_login();
cRender::show_time_options("$oApp->name&gt;$oTier->name"); 

cChart::do_header();

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

//####################################################################
$oCred = cRenderObjs::get_appd_credentials();
if ($oCred->restricted_login == null){
	cRenderMenus::show_tier_functions();
	cDebug::flush();
	
	cRenderMenus::show_tier_menu("Change Tier", "tier.php");
	cDebug::flush();
}
cRender::appdButton(cAppDynControllerUI::tier($oApp, $oTier));
cRender::appdButton(cAppDynControllerUI::tier_slow_transactions($oApp, $oTier),"Slow Transactions");
cDebug::flush();

//####################################################################
?><br><?php
cRender::button("Show Transactions", "../trans/apptrans.php?$sTierQS");
cDebug::flush();

?><h2>Activity for <?=cRender::show_name(cRender::NAME_APP,$oApp)?></h2><?php
	$aMetrics = [];
	$sMetricUrl=cAppDynMetric::appCallsPerMin();
	$aMetrics[]= [	cChart::LABEL=>"Overall Calls per min ($oApp->name) application", cChart::METRIC=>$sMetricUrl,cChart::STYLE=>cRender::getRowClass(),	];
	$sMetricUrl=cAppDynMetric::appResponseTimes();
	$aMetrics[]= [cChart::LABEL=>"Overall response time in ms ($oApp->name) application", cChart::METRIC=>$sMetricUrl];
	cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/2);

?><h2>Activity for <?=cRender::show_name(cRender::NAME_TIER,$oTier)?></h2><?php
	$aMetrics = [];
	$sMetricUrl=cAppDynMetric::tierCallsPerMin($oTier->name);	
	$sQs = cHttp::build_qs($sTierQS, cRender::METRIC_TYPE_QS, cAppDynMetric::METRIC_TYPE_ACTIVITY);
	$aMetrics[]= [
		cChart::LABEL=>"Calls per min for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,cChart::STYLE=>cRender::getRowClass(),
		cChart::GO_HINT=>"All Nodes",	cChart::GO_URL=>"tierallnodeinfra.php?$sQs"
	];
	
	$sQs = cHttp::build_qs($sTierQS, cRender::METRIC_TYPE_QS, cAppDynMetric::METRIC_TYPE_RESPONSE_TIMES);
	$sMetricUrl=cAppDynMetric::tierResponseTimes($oTier->name);
	$aMetrics[]= [
		cChart::LABEL=>"Response times in ms for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_HINT=>"All Nodes",	cChart::GO_URL=>"tierallnodeinfra.php?$sQs"
	];
	cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/2);
	cDebug::flush();
?>
<h2>Key Metrics for <?=cRender::show_name(cRender::NAME_TIER,$oTier)?></h2>
<?php
	$aMetrics = [];
	$aMetrics[] = [cChart::LABEL=>"Slow Calls", cChart::METRIC=>cAppDynMetric::tierSlowCalls($oTier->name),cChart::STYLE=>cRender::getRowClass()];
	$aMetrics[] = [cChart::LABEL=>"Very Slow Calls", cChart::METRIC=>cAppDynMetric::tierVerySlowCalls($oTier->name)];

	$sQs = cHttp::build_qs($sTierQS, cRender::METRIC_TYPE_QS, cAppDynInfraMetric::METRIC_TYPE_INFR_CPU_BUSY);
	$aMetrics[] = [
		cChart::LABEL=>"CPU Busy", cChart::METRIC=>cAppDynMetric::InfrastructureCpuBusy($oTier->name), cChart::HIDEIFNODATA=>1,
		cChart::GO_HINT=>"All Nodes",	cChart::GO_URL=>"tierallnodeinfra.php?$sQs"
	];
	
	$aMetrics[] = [
		cChart::LABEL=>"Disk Free", cChart::METRIC=>cAppDynMetric::InfrastructureDiskFree($oTier->name),cChart::STYLE=>cRender::getRowClass(), cChart::HIDEIFNODATA=>1
	];
	
	$sQs = cHttp::build_qs($sTierQS, cRender::METRIC_TYPE_QS, cAppDynMetric::METRIC_TYPE_ERRORS);
	$aMetrics[] = [
		cChart::LABEL=>"Errors Per Min", cChart::METRIC=>cAppDynMetric::tierErrorsPerMin($oTier->name), cChart::HIDEIFNODATA=>1,
		cChart::GO_HINT=>"All Nodes",	cChart::GO_URL=>"tierallnodeinfra.php?$sQs"
	];
	
	$aMetrics[] = [
		cChart::LABEL=>"Exceptions Per Min", cChart::METRIC=>cAppDynMetric::tierExceptionsPerMin($oTier->name), cChart::HIDEIFNODATA=>1
	];
	
	$sQs = cHttp::build_qs($sTierQS, cRender::METRIC_TYPE_QS, cAppDynInfraMetric::METRIC_TYPE_INFR_JAVA_HEAP_USED);
	$aMetrics[] = [
		cChart::LABEL=>"Java Heap Used", cChart::METRIC=>cAppDynMetric::InfrastructureJavaHeapUsed($oTier->name),cChart::STYLE=>cRender::getRowClass(), cChart::HIDEIFNODATA=>1,
		cChart::GO_HINT=>"All Nodes",	cChart::GO_URL=>"tierallnodeinfra.php?$sQs"
	];
	$aMetrics[] = [
		cChart::LABEL=>".Net Heap used", cChart::METRIC=>cAppDynMetric::InfrastructureDotnetHeapUsed($oTier->name), cChart::HIDEIFNODATA=>1
	];
	$aMetrics[] = [
		cChart::LABEL=>"Network In", cChart::METRIC=>cAppDynMetric::InfrastructureNetworkIncoming($oTier->name), cChart::HIDEIFNODATA=>1
	];
	$aMetrics[] = [
		cChart::LABEL=>"Network Out", cChart::METRIC=>cAppDynMetric::InfrastructureNetworkOutgoing($oTier->name),cChart::STYLE=>cRender::getRowClass(), cChart::HIDEIFNODATA=>1
	];
	$aMetrics[] = [
		cChart::LABEL=>"Machine Availability", cChart::METRIC=>cAppDynMetric::InfrastructureMachineAvailability($oTier->name), cChart::HIDEIFNODATA=>1
	];
	$aMetrics[] = [
		cChart::LABEL=>"Agent Availability", cChart::METRIC=>cAppDynMetric::InfrastructureAgentAvailability($oTier->name), cChart::HIDEIFNODATA=>1
	];
		
	cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);

//####################################################################
cChart::do_footer();
cRenderHtml::footer();
?>
