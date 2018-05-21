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
require_once("../inc/root.php");
cRoot::set_root("..");

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
		break;
	case cRender::METRIC_TYPE_RESPONSE_TIMES:
	case cRender::METRIC_TYPE_ACTIVITY:
	default:
		$sTitle1 = "Application Activity";
		$sMetric1 = cAppDynMetric::appCallsPerMin();
		$sTitle2 = "Application Response Times";
		$sMetric2 = cAppDynMetric::appResponseTimes();
		break;
}

//####################################################################
cRender::html_header("About");

//####################################################################
cRender::show_time_options( "About"); 
?>
		<h2>About the Reporter for Appdynamics&trade;</h2>

<?php
cRender::html_footer();
?>
