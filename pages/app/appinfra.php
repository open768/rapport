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


//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("Infrastructure");
cRender::force_login();
cChart::do_header();

//####################################################################
//####################################################################
$oApp = cRenderObjs::get_current_app();
$sTitle ="Infrastructure Overview for $oApp->name";

cRenderCards::card_start($sTitle);
	cRenderCards::body_start();
		echo "infrastructure metrics are shown for each tier below. metrics not available are hidden";
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRenderMenus::show_app_change_menu("Infrastructure");
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
$aActivityMetrics = [cADMetricPaths::METRIC_TYPE_ACTIVITY, cADMetricPaths::METRIC_TYPE_RESPONSE_TIMES];
$aMetricTypes = cADInfraMetric::getInfrastructureMetricTypes();

$aTiers =$oApp->GET_Tiers();
if (count($aTiers) == 0)
	cCommon::messagebox("no Tiers Found");
else{
	foreach ($aTiers as $oTier){
		cDebug::extra_debug("Tier: $oTier->name");
		$sTierQs = cRenderQS::get_base_tier_QS($oTier );
		$sAllUrl = cHttp::build_url("../tier/tierallnodeinfra.php", $sTierQs);

		$aMetrics = [];
		foreach ($aMetricTypes as $sMetricType){
			$oMetric = cADInfraMetric::getInfrastructureMetric($oTier->name,null,$sMetricType);
			$sUrl = cHttp::build_url($sAllUrl, cRenderQS::INFRA_METRIC_TYPE_QS, $sMetricType);
			cDebug::extra_debug( $sUrl);
			$aMetrics[] = [
				cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric, 
				cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See $oMetric->caption for all servers",
				cChart::HIDEIFNODATA=>1
			];
		}
		
		cRenderCards::card_start($oTier->name);
			cRenderCards::body_start();
				cChart::render_metrics($oApp,$aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
			cRenderCards::body_end();
			cRenderCards::action_start();
				cRenderMenus::show_tier_functions($oTier);
			cRenderCards::action_end();
		cRenderCards::card_end();

	}
}

//####################################################################
//####################################################################
cChart::do_footer();
cRenderHtml::footer();
?>
