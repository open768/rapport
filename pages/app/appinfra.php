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

?>
<h2><?=$sTitle?></h2>
<?php
//####################################################################
cRenderMenus::show_apps_menu("Infrastructure");
?>
<h2>Tiers</h2>
<?php
$aActivityMetrics = [cADMetricPaths::METRIC_TYPE_ACTIVITY, cADMetricPaths::METRIC_TYPE_RESPONSE_TIMES];
$aMetricTypes = cADInfraMetric::getInfrastructureMetricTypes();

$aTiers =$oApp->GET_Tiers();
foreach ($aTiers as $oTier){
	if (cFilter::isTierFilteredOut($oTier)) continue;
	
	cRenderMenus::show_tier_functions($oTier);
	$sTierQs = cRenderQS::get_base_tier_QS($oTier );
	$sAllUrl = cHttp::build_url("../tier/tierallnodeinfra.php", $sTierQs);

	$aMetrics = [];
	foreach ($aMetricTypes as $sMetricType){
		$oMetric = cADInfraMetric::getInfrastructureMetric($oTier->name,null,$sMetricType);
		$sUrl = cHttp::build_url($sAllUrl, cRenderQS::METRIC_TYPE_QS, $sMetricType);
		$aMetrics[] = [
			cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric, 
			cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See $oMetric->caption for all servers",
			cChart::HIDEIFNODATA=>1
		];
	}
	
	$sClass = cRender::getRowClass();
	cChart::render_metrics($oApp,$aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
}

//####################################################################
//####################################################################
cChart::do_footer();
cRenderHtml::footer();
?>
