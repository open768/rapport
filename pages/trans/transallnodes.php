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

require_once("$root/inc/filter.php");


//####################################################################
cRenderHtml::header("Transaction details for all nodes");
cRender::force_login();
cChart::do_header();

//####################################################
//display the results
$oTrans = cRenderObjs::get_current_trans();
$oTier = $oTrans->tier;
$oApp = $oTier->app;
$node= cHeader::get(cRender::NODE_QS);
$sExtraCaption = ($node?"($node) node":"");

$sTierQS = cRenderQS::get_base_tier_QS($oTier);
$sTransQS = cHttp::build_QS($sTierQS, cRender::TRANS_QS,$oTrans->name);
$sTransQS = cHttp::build_QS($sTransQS, cRender::TRANS_ID_QS,$oTrans->id);

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

//********************************************************************
$oCred = cRenderObjs::get_AD_credentials();
cADCommon::button(cADControllerUI::transaction($oTrans));
cDebug::flush();

// ################################################################################
// ################################################################################
$aNodes = $oTier->GET_Nodes();
function sort_nodes($a, $b){
	return strcmp($a->name, $b->name);
}
uasort($aNodes , "sort_nodes");

cRender::button("Back to Transaction", "transdetails.php?$sTransQS");
// ################################################################################
// ################################################################################
?><h2>Transaction: (<?=$oTrans->name?>) for all nodes</h2><?php

$aMetrics = [];
foreach ($aNodes as $oNode){ 
	$sNodeName = $oNode->name;
	$sNodeQs = cHttp::build_QS($sTransQS, cRender::NODE_QS, $oNode->name);
	$sUrl = "transdetails.php?$sNodeQs";
					
	$sMetricUrl=cADMetricPaths::transCallsPerMin($oTrans, $sNodeName);
	$aMetrics[] = [cChart::LABEL=>"Calls  ($sNodeName)", cChart::METRIC=>$sMetricUrl, cChart::GO_URL=>$sUrl, cChart::GO_HINT=>$oNode->name];
	$sMetricUrl=cADMetricPaths::transResponseTimes($oTrans, $sNodeName);
	$aMetrics[] = [cChart::LABEL=>"response ($sNodeName)", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cADMetricPaths::transErrors($oTrans, $sNodeName);
	$aMetrics[] = [cChart::LABEL=>"Errors ($sNodeName)", cChart::METRIC=>$sMetricUrl];
}
$sClass = cRender::getRowClass();
cChart::metrics_table($oApp, $aMetrics, 3, $sClass, cChart::CHART_HEIGHT_SMALL);

// ################################################################################
// ################################################################################
cChart::do_footer();

cRenderHtml::footer();
?>