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


//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();

//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("Tiers in Application $oApp->name");
cRender::force_login();
cChart::do_header();



$sPage = "tiers.php";
$sAppQS = cRenderQS::get_base_app_QS($oApp);
$sUrl = $sPage."?".$sAppQS;
if (cRender::is_list_mode()){
}else{
}
//####################################################################
$aResponse =$oApp->GET_Tiers();

if (cRender::is_list_mode()){
	cRenderCards::card_start("List of tiers in $oApp->name");
		cRenderCards::action_start();
			cRender::button("show as buttons", $sUrl);
		cRenderCards::action_end();
	cRenderCards::card_end();

	//*********************************************************************
	cRenderCards::card_start("there are ".count($aResponse)." tiers in this application");
		cRenderCards::body_start();
			echo "<ul>";
			foreach ( $aResponse as $oTier){
				$sTierQs=cRenderQS::get_base_tier_QS($oTier);
				$sUrl="../tier/tier.php?$sTierQs";
				$sTier=$oTier->name;
				echo "<li><a href='$sUrl'>$sTier</a><br>";
			}
			echo "</ul>";
		cRenderCards::body_end();
	cRenderCards::card_end();
	
}else{
	//####################################################################
	cRenderCards::card_start("Overall Activity in $oApp->name");
		cRenderCards::body_start();
			$aMetrics = [];
			$aMetrics[] = [cChart::LABEL=>"Overall Calls per min",cChart::METRIC=>cADAppMetricPaths::appCallsPerMin()];
			$aMetrics[] = [cChart::LABEL=>"Overall response time in ms", cChart::METRIC=>cADAppMetricPaths::appResponseTimes()];
			$aMetrics[] = [cChart::LABEL=>"Overall Errors per min", cChart::METRIC=>cADAppMetricPaths::appErrorsPerMin()];
			cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);			
		cRenderCards::body_end();
		cRenderCards::action_start();
			cRenderMenus::show_app_change_menu("Application Tier Activity for:");
			cADCommon::button(cADControllerUI::application($oApp));
			cADCommon::button(cADControllerUI::app_slow_transactions($oApp), "Slow Transactions");
			$sUrl.= "&".cRenderQS::LIST_MODE_QS;
			cRender::button("show as list", $sUrl);
			
			$sBaseMetric = cADAppMetricPaths::app();
			$sUrl = cHttp::build_url("../util/comparestats.php",$sAppQS);
			$sUrl = cHttp::build_url($sUrl,cRenderQS::METRIC_QS, $sBaseMetric );
			$sUrl = cHttp::build_url($sUrl,cRenderQS::TITLE_QS, "Application: $oApp->name" );
			cRender::button("compare statistics", $sUrl,true);
			
			cRender::add_filter_box("select[menu=tierfunctions]","tier",".mdl-card");
		cRenderCards::action_end();
	cRenderCards::card_end();
	

	//---TODO make this asynchronous--------------------------------------------
	foreach ( $aResponse as $oTier){
		$sTier=$oTier->name;
		
		$sTierQs=cRenderQS::get_base_tier_QS($oTier);
		
		$aMetrics = [];
		$aMetrics[] = [cChart::LABEL=>"Calls per min",cChart::METRIC=>cADTierMetricPaths::tierCallsPerMin($sTier), cChart::GO_HINT=>"Overview", cChart::GO_URL=>"../tier/tier.php?$sTierQs"];
		$aMetrics[] = [cChart::LABEL=>"Response time in ms", cChart::METRIC=>cADTierMetricPaths::tierResponseTimes($sTier)];
		$aMetrics[] = [cChart::LABEL=>"Errors per min", cChart::METRIC=>cADTierMetricPaths::tierErrorsPerMin($sTier)];
		cRenderCards::card_start();
			cRenderCards::body_start();
				cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);			
			cRenderCards::body_end();
			cRenderCards::action_start();
				cRenderMenus::show_tier_functions($oTier);
			cRenderCards::action_end();
		cRenderCards::card_end();
		echo "<p>";
	}
}
cChart::do_footer();

cRenderHtml::footer();
?>
