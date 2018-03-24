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
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################

require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/metrics.php");
require_once("$root/inc/inc-charts.php");
require_once("$phpinc/appdynamics/account.php");


//####################################################################
$sMetric = cHeader::get(cRender::METRIC_QS);
$oApp = cRenderObjs::get_current_app();
if (!$oApp->name) $oApp->name = "No Application Specified";
$sTitle = cHeader::get(cRender::TITLE_QS);

//####################################################################
cRender::html_header($sTitle);
cRender::force_login();
cRender::show_time_options("App: $oApp->name&gt; $sTitle"); 

//####################################################################
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE;
cChart::$show_zoom = false;

//####################################################################
$oItem = new cChartMetricItem();
$oItem->metric = $sMetric;
$oItem->caption = $sTitle;

$oChart = new cChartItem();
$oChart->metrics[] = $oItem;
$oChart->title = $sTitle;
$oChart->app = $oApp;
$oChart->height = 700;

?>
	<table class="maintable"><tr><td>
	<?php
		$oChart->write_html();
	?>
	</td></tr></table><?php

cChart::do_footer(false);
cRender::html_footer();
?>
