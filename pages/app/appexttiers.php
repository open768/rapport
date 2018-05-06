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

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}

//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();
$sExt = cHeader::get(cRender::BACKEND_QS);
$sAppQS = cRender::get_base_app_QS();

//####################################################################
$sTitle = "External Calls in $oApp->name for $sExt";
cRender::html_header($sTitle);
cRender::force_login();
cChart::do_header();
cRender::show_time_options( $sTitle); 


//####################################################################
cRenderMenus::show_app_functions();

//####################################################################
?>
<!-- ************************************************** -->
<h2><?=$sExt?></h2>
<?php
//-----------------------------------------------
$oResponse =$oApp->GET_Tiers();
$sMatched = cAppdynUtil::get_matching_extcall($oApp, $sExt);
if ($sMatched == null){
	cRender::errorbox("unable to find a matching external call: $sExt");
	exit;
}
$sExtQS = cHttp::build_qs($sAppQS, cRender::BACKEND_QS, $sMatched);

$aMetrics = [];


foreach ( $oResponse as $oTier){
	$sTier=$oTier->name;
	
	$sUrl = cHttp::build_qs($sExtQS, cRender::TIER_QS, $oTier->name);
	$sUrl = cHttp::build_qs($sUrl, cRender::TIER_ID_QS, $oTier->id);
	$sUrl = cHttp::build_url("$home/pages/tier/tierextalltrans.php", $sUrl);
	
	$aMetrics[] = [cChart::LABEL=>$sTier,cChart::TYPE=>cChart::LABEL, cChart::WIDTH=>300];
	$aMetrics[] = [cChart::LABEL=>"Calls per min",cChart::METRIC=>cAppDynMetric::tierExtCallsPerMin($sTier,$sMatched), cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"All Transactions"];
	$aMetrics[] = [cChart::LABEL=>"Response time in ms", cChart::METRIC=>cAppDynMetric::tierExtResponseTimes($sTier,$sMatched)];
}
cChart::metrics_table($oApp, $aMetrics,3,cRender::getRowClass());
cChart::do_footer();

cRender::html_footer();
?>
