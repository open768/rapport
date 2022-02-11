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

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();
$sExt = cHeader::get(cRenderQS::BACKEND_QS);
$sAppQS = cRenderQS::get_base_app_QS($oApp);

//####################################################################
$sTitle = "External Calls in $oApp->name for $sExt";
cRenderHtml::header($sTitle);
cRender::force_login();
cChart::do_header();

//####################################################################
cRenderMenus::show_app_functions();

//####################################################################
?>
<!-- ************************************************** -->
<h2><?=$sExt?></h2>
<?php
//-----------------------------------------------
$oResponse =$oApp->GET_Tiers();
$sMatched = cADUtil::get_matching_extcall($oApp, $sExt);
if ($sMatched == null){
	cCommon::errorbox("unable to find a matching external call: $sExt");
	exit;
}
$sExtQS = cHttp::build_qs($sAppQS, cRenderQS::BACKEND_QS, $sMatched);

$aMetrics = [];


foreach ( $oResponse as $oTier){
	$sTier=$oTier->name;
	
	$sUrl = cHttp::build_qs($sExtQS, cRenderQS::TIER_QS, $oTier->name);
	$sUrl = cHttp::build_qs($sUrl, cRenderQS::TIER_ID_QS, $oTier->id);
	$sUrl = cHttp::build_url("$home/pages/tier/tierextalltrans.php", $sUrl);
	
	$aMetrics[] = [cChart::LABEL=>$sTier,cChart::TYPE=>cChart::LABEL, cChart::WIDTH=>300];
	$aMetrics[] = [cChart::LABEL=>"Calls per min",cChart::METRIC=>cADMetricPaths::tierExtCallsPerMin($sTier,$sMatched), cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"All Transactions"];
	$aMetrics[] = [cChart::LABEL=>"Response time in ms", cChart::METRIC=>cADMetricPaths::tierExtResponseTimes($sTier,$sMatched)];
}
cChart::metrics_table($oApp, $aMetrics,3,cRender::getRowClass());
cChart::do_footer();

cRenderHtml::footer();
?>
