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
$home="..";
require_once "$home/inc/common.php";
require_once "$root/inc/inc-charts.php";


$sMetricType = cHeader::get(cRender::METRIC_TYPE_QS);
switch($sMetricType){
	case cAppDynMetric::METRIC_TYPE_RUMCALLS:
	case cAppDynMetric::METRIC_TYPE_RUMRESPONSE:
		$sTitle1 = "Web Browser Page Requests";
		$sMetric1 = cAppDynWebRumMetric::CallsPerMin();
		$sTitle2 = "Web Browser Page Response";
		$sMetric2 = cAppDynWebRumMetric::ResponseTimes();
		break;
	case cAppDynMetric::METRIC_TYPE_RESPONSE_TIMES:
	case cAppDynMetric::METRIC_TYPE_ACTIVITY:
	default:
		$sTitle1 = "Application Activity";
		$sMetric1 = cAppDynMetric::appCallsPerMin();
		$sTitle2 = "Application Response Times";
		$sMetric2 = cAppDynMetric::appResponseTimes();
		break;
}

//####################################################################
cRenderHtml::header("About");

//####################################################################
cRender::show_time_options( "About"); 
?>
		<h2>About the Reporter for Appdynamics&trade;</h2>

<?php
cRenderHtml::footer();
?>
