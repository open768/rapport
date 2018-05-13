<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
//####################################################################
$home = "../..";
$root=realpath($home);
$phpinc = realpath("$root/../phpinc");
$jsinc = "$home/../jsinc";

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
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");
require_once("$root/inc/inc-filter.php");


//####################################################################
cRender::html_header("Transaction details for all nodes");
cRender::force_login();
cChart::do_header();

//####################################################
//display the results
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$trans = cHeader::get(cRender::TRANS_QS);
$trid = cHeader::get(cRender::TRANS_ID_QS);
$node= cHeader::get(cRender::NODE_QS);
$sExtraCaption = ($node?"($node) node":"");

$sTierQS = cRender::get_base_tier_QS();
$sTransQS = cHttp::build_QS($sTierQS, cRender::TRANS_QS,$trans);
$sTransQS = cHttp::build_QS($sTransQS, cRender::TRANS_ID_QS,$trid);

cRender::show_time_options("$oApp->name&gt;$oApp->name&gt;$tier&gt;$trans"); 
//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}

//********************************************************************
$oCred = cRenderObjs::get_appd_credentials();
cRender::appdButton(cAppDynControllerUI::transaction($oApp,$trid));
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
?><h2>Transaction: (<?=$trans?>) for all nodes</h2><?php

$aMetrics = [];
foreach ($aNodes as $oNode){ 
	$sNodeName = $oNode->name;
	$sNodeQs = cHttp::build_QS($sTransQS, cRender::NODE_QS, $oNode->name);
	$sUrl = "transdetails.php?$sNodeQs";
					
	$sMetricUrl=cAppDynMetric::transCallsPerMin($oTier->name, $trans, $sNodeName);
	$aMetrics[] = [cChart::LABEL=>"Calls  ($sNodeName)", cChart::METRIC=>$sMetricUrl, cChart::GO_URL=>$sUrl, cChart::GO_HINT=>$oNode->name];
	$sMetricUrl=cAppDynMetric::transResponseTimes($oTier->name, $trans, $sNodeName);
	$aMetrics[] = [cChart::LABEL=>"response ($sNodeName)", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cAppDynMetric::transErrors($oTier->name, $trans, $sNodeName);
	$aMetrics[] = [cChart::LABEL=>"Errors ($sNodeName)", cChart::METRIC=>$sMetricUrl];
}
$sClass = cRender::getRowClass();
cChart::metrics_table($oApp, $aMetrics, 3, $sClass, cChart::CHART_HEIGHT_SMALL);

// ################################################################################
// ################################################################################
cChart::do_footer();

cRender::html_footer();
?>