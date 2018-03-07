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
$root=realpath(".");
$phpinc = realpath("$root/../phpinc");
$jsinc = "../jsinc";

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
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}

//-----------------------------------------------
$oTier = cRender::get_current_tier();
$oApp = $oTier->app;
$sExt = cHeader::get(cRender::BACKEND_QS);
$sAppQS = cRender::get_base_app_QS();
$sTierQS = cRender::get_base_tier_QS();

//####################################################################
$sTitle = "All transactions for External Call: $sExt";
cRender::html_header($sTitle);
cRender::force_login();
cChart::do_header();
cRender::show_time_options( $sTitle); 


//####################################################################
cRenderMenus::show_tier_functions();
$sExtQS = cHttp::build_qs($sAppQS, cRender::BACKEND_QS, $sExt);
$sUrl = cHttp::build_url("appexttiers.php", $sExtQS);

//####################################################################
?>
<!-- ************************************************** -->
<h2><?=$sTitle?></h2>
<?php
//-----------------------------------------------


$aTrans = cAppdyn::GET_tier_transaction_names($oApp->name, $oTier->name);

$aMetrics = [];
foreach ( $aTrans as $oTrans){
	$sUrl = cHttp::build_qs($sTierQS, cRender::TRANS_QS, $oTrans->name);
	$sUrl = cHttp::build_qs($sUrl, cRender::TRANS_ID_QS, $oTrans->id);
	$sUrl = cHttp::build_url("transdetails.php", $sUrl);
	
	$aMetrics[] = [cChart::LABEL=>$oTrans->name,cChart::TYPE=>cChart::LABEL, cChart::WIDTH=>300];
	$aMetrics[] = [
		cChart::LABEL=>"$oTrans->name to External Call Calls per min ",
		cChart::METRIC=>cAppDynMetric::transCallsPerMin($oTier->name,$oTrans->name),
		cChart::GO_URL => $sUrl,
		cChart::GO_HINT => "Transaction"
	];
	$aMetrics[] = [cChart::LABEL=>"$oTrans->name to External Call Response time in ms", cChart::METRIC=>cAppDynMetric::transResponseTimes($oTier->name,$oTrans->name)];
}
cChart::metrics_table($oApp, $aMetrics,3,cRender::getRowClass());
cChart::do_footer();

cRender::html_footer();
?>
