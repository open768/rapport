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
$home = "../..";
$root=realpath($home);
$phpinc = realpath("$root/../phpinc");
$jsinc = "$home/../jsinc";

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");


$sMetricType = cHeader::get(cRender::METRIC_TYPE_QS);
switch($sMetricType){
	case cRender::METRIC_TYPE_RUMCALLS:
	case cRender::METRIC_TYPE_RUMRESPONSE:
		$sTitle1 = "Web Browser Page Requests";
		$sMetric1 = cAppDynMetric::webrumCallsPerMin();
		$sTitle2 = "Web Browser Page Response";
		$sMetric2 = cAppDynMetric::webrumResponseTimes();
		$sTitle3 = "Pages With Javascript Errors";
		$sMetric3 = cAppDynMetric::webrumJavaScriptErrors();
		
		$sBaseUrl = "apprum.php";
		break;
	case cRender::METRIC_TYPE_RESPONSE_TIMES:
	case cRender::METRIC_TYPE_ACTIVITY:
	default:
		$sTitle1 = "Application Activity";
		$sMetric1 = cAppDynMetric::appCallsPerMin();
		$sTitle2 = "Application Response Times";
		$sMetric2 = cAppDynMetric::appResponseTimes();
		$sTitle3 = "Application Errors";
		$sMetric3 = cAppDynMetric::appErrorsPerMin();
		$sBaseUrl = "$home/pages/app/tiers.php";
		break;
}

//####################################################################
cRender::html_header("All Applications - $sTitle1");
cRender::force_login();
cChart::do_header();

//####################################################################
cRender::show_time_options( "All Applications - $sTitle1"); 		
cRender::appdButton(cAppDynControllerUI::apps_home());
?>
	<h1><?=$sTitle1?></h1>
<?php

//####################################################################
$aResponse = cAppDyn::GET_Applications();
if ( count($aResponse) == 0)
	cRender::messagebox("Nothing found");
else{
	//display the results
	foreach ( $aResponse as $oApp){
		if (cFilter::isAppFilteredOut($oApp->name)) continue;
		$sUrl = cHttp::build_url($sBaseUrl, cRender::build_app_qs($oApp));
		$aMetrics = [
			[cChart::LABEL=>$sTitle1, cChart::METRIC=>$sMetric1, cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"detail for $oApp->name"],
			[cChart::LABEL=>$sTitle2, cChart::METRIC=>$sMetric2],
			[cChart::LABEL=>$sTitle3, cChart::METRIC=>$sMetric3]
		];
		cRenderMenus::show_app_functions($oApp);
		cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
		?><br><?php
	}
}
cChart::do_footer();
cRender::html_footer();
?>
