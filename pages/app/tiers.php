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
cRenderHtml::header("Tiers in Application $oApp->name");
cRender::force_login();
cChart::do_header();



$sPage = "tiers.php";
$sBaseQS = cRenderQS::get_base_app_QS($oApp);
$sUrl = $sPage."?".$sBaseQS;
if (cRender::is_list_mode()){
}else{
}
//####################################################################
$aResponse =$oApp->GET_Tiers();

if (cRender::is_list_mode()){
	cRenderCards::card_start();
		cRenderCards::title_start();
			?>List of tiers in <?=cRender::show_name(cRender::NAME_APP,$oApp)?><?php
		cRenderCards::title_end();
		cRenderCards::action_start();
			cRender::button("show as buttons", $sUrl);
		cRenderCards::action_end();
	cRenderCards::card_end();

	//*********************************************************************
	cRenderCards::card_start();
		cRenderCards::title_start();
			echo "there are ".count($aResponse)." tiers in this application";
		cRenderCards::title_end();
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
	cRenderCards::card_start();
		cRenderCards::body_start();
			?><h4>Overall Activity in <?=cRender::show_name(cRender::NAME_APP,$oApp)?></h4><?php
			$aMetrics = [];
			$aMetrics[] = [cChart::LABEL=>"Overall Calls per min",cChart::METRIC=>cADMetric::appCallsPerMin()];
			$aMetrics[] = [cChart::LABEL=>"Overall response time in ms", cChart::METRIC=>cADMetric::appResponseTimes()];
			$aMetrics[] = [cChart::LABEL=>"Overall Errors per min", cChart::METRIC=>cADMetric::appErrorsPerMin()];
			cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);			
		cRenderCards::body_end();
		cRenderCards::action_start();
			cRenderMenus::show_apps_menu("Application Tier Activity for:","tiers.php");
			cADCommon::button(cADControllerUI::application($oApp));
			cADCommon::button(cADControllerUI::app_slow_transactions($oApp), "Slow Transactions");
			$sUrl.= "&".cRender::LIST_MODE_QS;
			cRender::button("show as list", $sUrl);
			cRender::add_filter_box("select[menu=tierfunctions]","tier",".mdl-card");
		cRenderCards::action_end();
	cRenderCards::card_end();
	

	//-----------------------------------------------
	foreach ( $aResponse as $oTier){
		$sTier=$oTier->name;
		if (cFilter::isTierFilteredOut($oTier)) continue;
		
		$sTierQs=cRenderQS::get_base_tier_QS($oTier);
		
		$aMetrics = [];
		$aMetrics[] = [cChart::LABEL=>"Calls per min",cChart::METRIC=>cADMetric::tierCallsPerMin($sTier), cChart::GO_HINT=>"Overview", cChart::GO_URL=>"../tier/tier.php?$sTierQs"];
		$aMetrics[] = [cChart::LABEL=>"Response time in ms", cChart::METRIC=>cADMetric::tierResponseTimes($sTier)];
		$aMetrics[] = [cChart::LABEL=>"Errors per min", cChart::METRIC=>cADMetric::tierErrorsPerMin($sTier)];
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
