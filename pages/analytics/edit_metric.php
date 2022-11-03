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

//####################################################################
cRenderHtml::header("Edit Metrics");
cRender::force_login();

$sMetric = cHeader::GET(cRenderQS::ANALYTICS_METRIC_QS);
if (!$sMetric){
	cDebug::error("missing metric name");
}

$oMetric = cADAnalytics::get_metric($sMetric);
if (! $oMetric){
	cDebug::error("couldnt find metric with name: $sMetric");
}


//####################################################################
cRenderCards::card_start("Edit Metric");
	cRenderCards::body_start();
		cCommon::messagebox("Found Metric with name: $sMetric TBD");
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRender::button("back to metrics","metrics.php");
	cRenderCards::action_end();
cRenderCards::card_end();

	
cRenderHtml::footer();
?>