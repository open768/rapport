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



cRenderHtml::$load_google_charts = true;
cRenderHtml::header("Application node detail ");
cRender::force_login();
$oApp = cRenderObjs::get_current_app();
$gsMetricType = cHeader::get(cRenderQS::METRIC_TYPE_QS);
$sAppQS = cRenderQS::get_base_app_QS($oApp);

//####################################################################
cChart::do_header();
cChart::$width = cChart::CHART_WIDTH_LARGE;
//####################################################################
function pr__sort_nodes($a,$b){
	return strcmp($a[0]->tierName, $b[0]->tierName);
}

function group_by_tier($paNodes){
	$aTiers = [];
	
	foreach ($paNodes as $aTierNodes)
		foreach ($aTierNodes as $oNode){
			$TierID = $oNode->tierId;
			if (!isset($aTiers[(string)$TierID])) $aTiers[(string)$TierID] = [];
			$aTiers[(string)$TierID][] = $oNode;
		}
		
	return $aTiers;
}

function count_nodes($paData){
	$iCount = 0;
	foreach ($paData as $aTierNodes)
		$iCount += count($aTierNodes);
		
	return $iCount;
}

//####################################################################
if (!$oApp->name ){
	cCommon::errorbox("no application");
	exit;
}
if (!$gsMetricType ){
	cCommon::errorbox("no metric type");
	exit;
}
$oMetric = cADInfraMetric::getInfrastructureMetric($oApp->name,null,$gsMetricType);

//####################################################################
$sAppQS = cRenderQS::get_base_app_QS($oApp);

$sDetailRootQS = cHttp::build_url(cCommon::filename(), $sAppQS);

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

$aResponse = $oApp->GET_Nodes();
$iNodes = count_nodes($aResponse);
if ($iNodes==0){
	cCommon::messagebox("no nodes found");
	cRenderHtml::footer();
	exit;
}

//####################################################################
$sTitle  = $oMetric->caption;
cRenderCards::card_start($sTitle);
	cRenderCards::action_start();
		cRenderMenus::show_app_agent_menu();
		cRenderMenus::show_app_change_menu("Show detail for", cHttp::build_url(cCommon::filename(),cRenderQS::METRIC_TYPE_QS,$gsMetricType));
		cADCommon::button(cADControllerUI::nodes($oApp), "All nodes");
	cRenderCards::action_end();
cRenderCards::card_end();
		
//####################################################################
$aResponse= group_by_tier($aResponse);
uasort($aResponse, "pr__sort_nodes");

foreach ($aResponse as $aTierNodes){
	
	$tid = $aTierNodes[0]->tierId;
	$sTier = $aTierNodes[0]->tierName;

	cRenderCards::card_start($sTier);
		cRenderCards::body_start();
			$oTier = cRenderObjs::make_tier_obj($oApp, $sTier, $tid);
			

			$sTierQS = cHttp::build_qs($sAppQS, cRenderQS::TIER_QS, $sTier);
			$sTierQS = cHttp::build_qs($sTierQS , cRenderQS::TIER_ID_QS, $tid);
			$sTierRootUrl=cHttp::build_url("tierinfrstats.php",$sTierQS);				
			$aMetrics = [];
			foreach ($aTierNodes as $oNode){
				$sNode = $oNode->name;

				$oMetric = cADInfraMetric::getInfrastructureMetric($sTier, $sNode ,$gsMetricType );
				$sDetailUrl = cHttp::build_url($sTierRootUrl,cRenderQS::NODE_QS,$sNode);
				
				$aMetrics[] = [cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric];
				$aMetrics[] = [cChart::TYPE=>cChart::LABEL,cChart::LABEL=>cRender::button_code("go",$sDetailUrl)];
				
			}
			cChart::metrics_table($oApp, $aMetrics,6,cRender::getRowClass(),null,cChart::CHART_WIDTH_LETTERBOX/3);
		cRenderCards::body_end();
		cRenderCards::action_start();
			cRenderMenus::show_tier_functions($oTier);
		cRenderCards::action_end();
	cRenderCards::card_end();
}

cChart::do_footer();
cRenderHtml::footer();
?>
