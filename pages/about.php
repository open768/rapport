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
$home="..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";


$sMetricType = cHeader::get(cRender::METRIC_TYPE_QS);
switch($sMetricType){
	case cADMetric::METRIC_TYPE_RUMCALLS:
	case cADMetric::METRIC_TYPE_RUMRESPONSE:
		$sTitle1 = "Web Browser Page Requests";
		$sMetric1 = cADWebRumMetric::CallsPerMin();
		$sTitle2 = "Web Browser Page Response";
		$sMetric2 = cADWebRumMetric::ResponseTimes();
		break;
	case cADMetric::METRIC_TYPE_RESPONSE_TIMES:
	case cADMetric::METRIC_TYPE_ACTIVITY:
	default:
		$sTitle1 = "Application Activity";
		$sMetric1 = cADMetric::appCallsPerMin();
		$sTitle2 = "Application Response Times";
		$sMetric2 = cADMetric::appResponseTimes();
		break;
}

//####################################################################
cRenderHtml::header("About");

//####################################################################
cRender::show_top_banner( "About"); 
?>
		<h2>About the Rapport - an Interactive Reporter for Appdynamics&trade;</h2>

<?php
cRenderHtml::footer();
?>
