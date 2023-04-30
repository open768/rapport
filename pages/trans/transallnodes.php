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

//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("Transaction details for all nodes");
cRender::force_login();
cChart::do_header();

//####################################################
//display the results
/** @var cADTrans */ 	$oTrans = cRenderObjs::get_current_trans();
/** @var cADTier  */ 	$oTier = $oTrans->tier;		
/** @var cADApp  */		$oApp = $oTier->app;
$node= cHeader::get(cRenderQS::NODE_QS);
$sExtraCaption = ($node?"($node) node":"");

$sTierQS = cRenderQS::get_base_tier_QS($oTier);
$sTransQS = cHttp::build_QS($sTierQS, cRenderQS::TRANS_QS,$oTrans->name);
$sTransQS = cHttp::build_QS($sTransQS, cRenderQS::TRANS_ID_QS,$oTrans->id);

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

//********************************************************************
$oCred = cRenderObjs::get_AD_credentials();
cDebug::flush();

// ################################################################################
// ################################################################################
$aNodes = $oTier->GET_Nodes();

// ################################################################################
// ################################################################################
cRenderCards::card_start("Transaction: $oTrans->name - for all nodes");
	cRenderCards::action_start();
		cRender::button("Back to Transaction", "transdetails.php?$sTransQS");
		cADCommon::button(cADControllerUI::transaction($oTrans));
	cRenderCards::action_end();
cRenderCards::card_end();

foreach ($aNodes as $oNode){ 
	$sNodeName = $oNode->name;
	$sNodeQs = cHttp::build_QS($sTransQS, cRenderQS::NODE_QS, $oNode->name);
	$sUrl = "transdetails.php?$sNodeQs";
					
	$aMetrics = [];
	$sMetricUrl=cADMetricPaths::transCallsPerMin($oTrans, $sNodeName);
	$aMetrics[] = [cChart::LABEL=>"Calls  ($sNodeName)", cChart::METRIC=>$sMetricUrl, cChart::GO_URL=>$sUrl, cChart::GO_HINT=>$oNode->name];
	$sMetricUrl=cADMetricPaths::transResponseTimes($oTrans, $sNodeName);
	$aMetrics[] = [cChart::LABEL=>"response ($sNodeName)", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cADMetricPaths::transErrors($oTrans, $sNodeName);
	$aMetrics[] = [cChart::LABEL=>"Errors ($sNodeName)", cChart::METRIC=>$sMetricUrl];

	cRenderCards::card_start("Node: $sNodeName");
		cRenderCards::body_start();
			$sClass = cRender::getRowClass();
			cChart::metrics_table($oApp, $aMetrics, 3, $sClass, cChart::CHART_HEIGHT_SMALL);
		cRenderCards::body_end();
	cRenderCards::card_end();
}

// ################################################################################
// ################################################################################
cChart::do_footer();

cRenderHtml::footer();
?>