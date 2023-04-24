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


//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("tier infrastructure");
cRender::force_login();
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE;

//####################################################################
//get passed in values
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$node = cHeader::get(cRenderQS::NODE_QS);

//####################################################################
if (!$oTier->name){
	cCommon::errorbox("no Tier parameter found");
	exit;
}
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

//####################################################################
// huge time limit as this takes a long time//display the results
set_time_limit(200); 


//####################################################################
//stuff for later
$sAppQs = cRenderQS::get_base_app_QS($oApp);
$sTierQs = cRenderQS::get_base_tier_QS($oTier);
$sTierInfraUrl = cHttp::build_url(cCommon::filename(),$sTierQs);
$sAppInfraUrl = cHttp::build_url("../app/appinfra.php",$sAppQs);
$oApp = cRenderObjs::get_current_app();
$oCred = cRenderObjs::get_AD_credentials();


//####################################################################
//# CARD
//####################################################################
$sTitle = "Tier Infrastructure for $oTier->name, ".($node?"($node) Server":"all Servers");
cRenderCards::card_start($sTitle);
	cRenderCards::body_start();
	?><ul>
		<li><a href=#agent>Agent Statistics</a></li>
		<li><a href=#mem>Memory Statistics</a></li>
		<li><a href=#infr>Infrastructure Statistics</a></li>
	</ul>
	<?php
	cRenderCards::body_end();

	cRenderCards::action_start();
		if ($oCred->restricted_login == null)	cRenderMenus::show_tier_functions();

		cRenderMenus::show_tier_infra_menu($oTier, $node);
		if ($node) {
			$sNodeID = cADUtil::get_node_id($oApp, $node);
			if ($sNodeID){
				$sUrl = cADControllerUI::nodeDashboard($oApp, $sNodeID);
				cADCommon::button($sUrl);
			}
		}
		$sDiskUrl = cHttp::build_url("tierdisks.php", $sTierQs);
		cRender::button("disk statistics", $sDiskUrl);
	cRenderCards::action_end();
cRenderCards::card_end();


//####################################################################
//# CARD
//####################################################################
$sAllUrl = cHttp::build_url("tierallnodeinfra.php", $sTierQs);
cRenderCards::card_start("Overall Statistics");
cRenderCards::body_start();
	$aMetrics = [];
	$sMetricUrl=cADTierMetricPaths::tierCallsPerMin($oTier->name);
	$sUrl = cHttp::build_url($sAllUrl, cRenderQS::METRIC_TYPE_QS, cADMetricPaths::METRIC_TYPE_ACTIVITY);
	$aMetrics[]= [
		cChart::LABEL=>"Calls per min for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,cChart::STYLE=>cRender::getRowClass(),
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See Activity for all nodes in Tier:$oTier->name"
	];
	
	$sMetricUrl=cADTierMetricPaths::tierResponseTimes($oTier->name);
	$sUrl = cHttp::build_url($sAllUrl, cRenderQS::METRIC_TYPE_QS, cADMetricPaths::METRIC_TYPE_RESPONSE_TIMES);
	$aMetrics[]= [
		cChart::LABEL=>"Response times in ms for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See Response Times for all nodes in Tier:$oTier->name"
	];
	cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
	cRenderCards::body_end();
cRenderCards::card_end();

//####################################################################
//# CARD
//####################################################################
cRenderCards::card_start("<a name='agent'>Agent Statistics</a>");
cRenderCards::body_start();
	$aMetricTypes = cADInfraMetric::getInfrastructureAgentMetricTypes();
	
	$aMetrics = [];
	foreach ($aMetricTypes as $sMetricType){
		$oMetric = cADInfraMetric::getInfrastructureMetric($oTier->name,$node,$sMetricType);
		$sUrl = cHttp::build_url($sAllUrl, cRenderQS::METRIC_TYPE_QS, $sMetricType);
		$aMetrics[]= [
			cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric, 
			cChart::HIDEIFNODATA=>true,
			cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See $oMetric->caption for all nodes in Tier:$oTier->name"
		];
	}
	cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
	cRenderCards::body_end();
cRenderCards::card_end();

//####################################################################
//# CARD
//####################################################################
cRenderCards::card_start("<a name='mem'>Memory Statistics</a>");
cRenderCards::body_start();
	$aMetricTypes = cADInfraMetric::getInfrastructureMemoryMetricTypes();
	
	$aMetrics = [];
	foreach ($aMetricTypes as $sMetricType){
		$oMetric = cADInfraMetric::getInfrastructureMetric($oTier->name,$node,$sMetricType);
		$sUrl = cHttp::build_url($sAllUrl, cRenderQS::METRIC_TYPE_QS, $sMetricType);
		$aMetrics[]= [
			cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric, 
			cChart::HIDEIFNODATA=>true,
			cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See $oMetric->caption for all nodes in Tier:$oTier->name"
		];
	}
	cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
	cRenderCards::body_end();
cRenderCards::card_end();

//####################################################################
//# CARD
//####################################################################
cRenderCards::card_start("<a name='infr'>Infrastructure Statistics</a>");
cRenderCards::body_start();
	$aMetricTypes = cADInfraMetric::getInfrastructureMiscMetricTypes();
	
	$aMetrics = [];
	foreach ($aMetricTypes as $sMetricType){
		$oMetric = cADInfraMetric::getInfrastructureMetric($oTier->name,$node,$sMetricType);
		$sUrl = cHttp::build_url($sAllUrl, cRenderQS::METRIC_TYPE_QS, $sMetricType);
		$aMetrics[]= [
			cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric, 
			cChart::HIDEIFNODATA=>true,
			cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See $oMetric->caption for all nodes in Tier:$oTier->name"
		];
	}
	cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
	cRenderCards::body_end();
cRenderCards::card_end();

	
cChart::do_footer();
cRenderHtml::footer();
?>
