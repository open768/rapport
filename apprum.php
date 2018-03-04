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
$root=realpath(".");
$phpinc = realpath("$root/../phpinc");
$jsinc = "../jsinc";

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/http.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


//-----------------------------------------------
$oApp = cRender::get_current_app();
$sAppQS = cRender::get_base_app_QS();


//####################################################################
cRender::html_header("Web browser - Real user monitoring");
cRender::force_login();
cChart::do_header();

$title ="$oApp->name&gt;Web Real User Monitoring";
cRender::show_time_options( $title); 

cRenderMenus::show_apps_menu("Show Web RUM for:", "apprum.php");
cRender::appdButton(cAppDynControllerUI::webrum($oApp->id));

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************

//####################################################################
?><h2>Overall Statistics</h2><?php
$aMetrics = [];
$aMetrics[] = [cChart::LABEL=>"Overall Calls per min",cChart::METRIC=>cAppDynMetric::appCallsPerMin()];
$aMetrics[] = [cChart::LABEL=>"Overall response time in ms", cChart::METRIC=>cAppDynMetric::appResponseTimes()];
cChart::metrics_table($oApp, $aMetrics,2,cRender::getRowClass());			

?><h2>Browser Stats for (<?=$oApp->name?>)</h2><?php
cRender::button("Show Page Statistics", "rumstats.php?$sAppQS");
$aMetrics = [];
$aMetrics[] = [cChart::LABEL=>"Page requests per minute",cChart::METRIC=>cAppDynMetric::webrumCallsPerMin()];
$aMetrics[] = [cChart::LABEL=>"Page response time",cChart::METRIC=>cAppDynMetric::webrumResponseTimes()];
$aMetrics[] = [cChart::LABEL=>"Page connection time",cChart::METRIC=>cAppDynMetric::webrumTCPTime()];
$aMetrics[] = [cChart::LABEL=>"Page Server time",cChart::METRIC=>cAppDynMetric::webrumServerTime()];
$aMetrics[] = [cChart::LABEL=>"Page first byte time",cChart::METRIC=>cAppDynMetric::webrumFirstByte()];
cChart::metrics_table($oApp, $aMetrics,2,cRender::getRowClass());			


cChart::do_footer();

cRender::html_footer();
?>
