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
require_once "$root/inc/inc-charts.php";


//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();

//####################################################################
cRenderHtml::header("Tiers in Application $oApp->name");
cRender::force_login();
cChart::do_header();
cRender::show_time_options( $oApp->name); 


//####################################################################
cRenderMenus::show_apps_menu("Application Tier Activity for:","tiers.php");
$sAppQS = cRenderQS::get_base_app_QS($oApp);

cRender::appdButton(cAppDynControllerUI::application($oApp));
cRender::appdButton(cAppDynControllerUI::app_slow_transactions($oApp), "Slow Transactions");

//####################################################################
?>
<h2>Overall Activity in <?=cRender::show_name(cRender::NAME_APP,$oApp)?></h2>
<?php
$aMetrics = [];
$aMetrics[] = [cChart::LABEL=>"Overall Calls per min",cChart::METRIC=>cAppDynMetric::appCallsPerMin()];
$aMetrics[] = [cChart::LABEL=>"Overall response time in ms", cChart::METRIC=>cAppDynMetric::appResponseTimes()];
$aMetrics[] = [cChart::LABEL=>"Overall Errors per min", cChart::METRIC=>cAppDynMetric::appErrorsPerMin()];
cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);			
?>
<p>
<!-- ************************************************** -->
<h2>Tier Activity <?=cRender::show_name(cRender::NAME_APP,$oApp)?></h2>
<?php
//-----------------------------------------------
$oResponse =$oApp->GET_Tiers();
foreach ( $oResponse as $oTier){
	$sTier=$oTier->name;
	if (cFilter::isTierFilteredOut($oTier)) continue;
	
	$sTierQs=cRenderQS::get_base_tier_QS($oTier);
	
	cRenderMenus::show_tier_functions($oTier);
	$aMetrics = [];
	$aMetrics[] = [cChart::LABEL=>"Calls per min",cChart::METRIC=>cAppDynMetric::tierCallsPerMin($sTier), cChart::GO_HINT=>"Overview", cChart::GO_URL=>"../tier/tier.php?$sTierQs"];
	$aMetrics[] = [cChart::LABEL=>"Response time in ms", cChart::METRIC=>cAppDynMetric::tierResponseTimes($sTier)];
	$aMetrics[] = [cChart::LABEL=>"Errors per min", cChart::METRIC=>cAppDynMetric::tierErrorsPerMin($sTier)];
	cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);			
}
cChart::do_footer();

cRenderHtml::footer();
?>
