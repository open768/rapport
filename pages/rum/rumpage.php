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
require_once("../../inc/root.php");
cRoot::set_root("../..");

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


//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();
$rum_page = cHeader::get(cRender::RUM_PAGE_QS);
$rum_type = cHeader::get(cRender::RUM_TYPE_QS);
$gsAppQS = cRender::get_base_app_QS();

//####################################################################
$title ="$oApp->name&gtWeb Real User Monitoring Details&gt;$rum_page";
cRender::html_header("Web browser - Real user monitoring - $rum_page");
cRender::show_time_options( $title); 
cRender::force_login();
cChart::do_header();

cRenderMenus::show_app_functions($oApp);
cRender::button("Back to page requests", "rumstats.php?$gsAppQS");
cRender::appdButton(cAppDynControllerUI::webrum($oApp));

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************

//####################################################################

?><H2>Real User Monitoring Details for (<?=$rum_page?>)</h2><?php
$aMetrics = [];
$sMetricUrl=cAppDynMetric::webrumPageCallsPerMin($rum_type, $rum_page);
$aMetrics[] = [cChart::LABEL=>"Page requests: $rum_page", cChart::METRIC=>$sMetricUrl];
$sMetricUrl=cAppDynMetric::webrumPageResponseTimes($rum_type, $rum_page);
$aMetrics[] = [cChart::LABEL=>"Page Response times: $rum_page", cChart::METRIC=>$sMetricUrl];
$sMetricUrl=cAppDynMetric::webrumPageTCPTime($rum_type, $rum_page);
$aMetrics[] = [cChart::LABEL=>"Page connection time", cChart::METRIC=>$sMetricUrl];
$sMetricUrl=cAppDynMetric::webrumPageServerTime($rum_type, $rum_page);
$aMetrics[] = [cChart::LABEL=>"Page Server time", cChart::METRIC=>$sMetricUrl];
$sMetricUrl=cAppDynMetric::webrumPageFirstByte($rum_type, $rum_page);
$aMetrics[] = [cChart::LABEL=>"Page first byte time", cChart::METRIC=>$sMetricUrl];
cChart::metrics_table($oApp, $aMetrics,2,cRender::getRowClass());			

cChart::do_footer();
cRender::html_footer();
?>
