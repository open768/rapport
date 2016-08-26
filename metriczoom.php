<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2016 

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
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################

require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/metrics.php");
require_once("inc/inc-charts.php");
require_once("$phpinc/appdynamics/account.php");


//####################################################################
$sMetric = cHeader::get(cRender::METRIC_QS);
$sApp = cHeader::get(cRender::APP_QS);
if (!$sApp) $sApp = "No Application Specified";
$sTitle = cHeader::get(cRender::TITLE_QS);

//####################################################################
cRender::html_header($sTitle);
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	<script type="text/javascript" src="js/chart.php"></script>
<?php
cRender::show_time_options("App: $sApp&gt; $sTitle"); 

//####################################################################
cChart::do_header();
cChart::$width=cRender::CHART_WIDTH_LARGE;
cChart::$showZoom = false;
cChart::$json_data_fn = "chart_getUrl";
cChart::$json_callback_fn = "chart_jsonCallBack";
cChart::$csv_url = "rest/getMetric.php";
cChart::$csv_url = "rest/getMetric.php";

cChart::$save_fn = "save_fave_chart";
cChart::$compare_url = "compare.php";
cChart::$metric_qs = cRender::METRIC_QS;
cChart::$title_qs = cRender::TITLE_QS;
cChart::$app_qs = cRender::APP_QS;

//####################################################################

?>
	<table class="maintable"><tr><td>
	<?php
		cChart::add($sTitle, $sMetric, $sApp, 700);
	?>
	</td></tr></table><?php

cChart::do_footer();
cRender::html_footer();
?>
