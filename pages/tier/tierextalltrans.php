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
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$sExt = cHeader::get(cRender::BACKEND_QS);
$sAppQS = cRenderQS::get_base_app_QS($oApp);
$sTierQS = cRenderQS::get_base_tier_QS($oTier);

//####################################################################
$sTitle = "External Service: $sExt";
cRenderHtml::header($sTitle);
cRender::force_login();
cChart::do_header();

//####################################################################
$sExtQS = cHttp::build_qs($sAppQS, cRender::BACKEND_QS, $sExt);

//####################################################################
cRenderCards::card_start("All Calls from $oTier->name to $sExt");
cRenderCards::body_start();
	$aMetrics = [];
	$aMetrics[] = [cChart::LABEL=>"$oTier->name - Calls per min",cChart::METRIC=>cADMetricPaths::tierExtCallsPerMin($oTier->name,$sExt)];
	$aMetrics[] = [cChart::LABEL=>"$oTier->name - Response time in ms", cChart::METRIC=>cADMetricPaths::tierExtResponseTimes($oTier->name,$sExt)];
	cChart::metrics_table($oApp, $aMetrics,2,cRender::getRowClass());
cRenderCards::body_end();
cRenderCards::action_start();
	cRenderMenus::show_tier_functions();
cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
$aTrans = $oTier->GET_all_transaction_names();
$aMetrics = [];

cRenderCards::card_start("Transactions");
cRenderCards::body_start();
	foreach ( $aTrans as $oTrans){
		$sUrl = cHttp::build_qs($sTierQS, cRender::TRANS_QS, $oTrans->name);
		$sUrl = cHttp::build_qs($sUrl, cRender::TRANS_ID_QS, $oTrans->id);
		$sUrl = cHttp::build_url("$home/pages/trans/transdetails.php", $sUrl);
		
		$aMetrics[] = [
			cChart::LABEL=>"$oTrans->name to External - Calls per min ",
			cChart::METRIC=>cADMetricPaths::transExtCalls($oTrans, $sExt),
			cChart::GO_URL => $sUrl,
			cChart::GO_HINT => "Transaction",
			cChart::HIDEIFNODATA=>1
		];
		$aMetrics[] = [
			cChart::LABEL=>"$oTrans->name to External - Response time in ms", 
			cChart::METRIC=>cADMetricPaths::transExtResponseTimes($oTrans,$sExt),
			cChart::HIDEIFNODATA=>1
		];
		$aMetrics[] = [
			cChart::LABEL=>"$oTrans->name to External - errors", 
			cChart::METRIC=>cADMetricPaths::transExtErrors($oTrans,$sExt),
			cChart::HIDEIFNODATA=>1
		];
	}
	cChart::metrics_table($oApp, $aMetrics,3,cRender::getRowClass());
cRenderCards::body_end();
cRenderCards::card_end();
	
	
cChart::do_footer();

cRenderHtml::footer();
?>
