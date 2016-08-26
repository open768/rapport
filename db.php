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
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");

$sDB = cHeader::get(cRender::DB_QS);


//####################################################################
cRender::html_header("Database - $sDB");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	<script type="text/javascript" src="js/chart.php"></script>
<?php
cChart::do_header();
cChart::$width=cRender::CHART_WIDTH_LARGE/2;
cChart::$json_data_fn = "chart_getUrl";
cChart::$json_callback_fn = "chart_jsonCallBack";
cChart::$csv_url = "rest/getMetric.php";
cChart::$zoom_url = "metriczoom.php";
cChart::$save_fn = "save_fave_chart";

cChart::$compare_url = "compare.php";
cChart::$metric_qs = cRender::METRIC_QS;
cChart::$title_qs = cRender::TITLE_QS;
cChart::$app_qs = cRender::APP_QS;

//####################################################################
cRender::show_time_options( "Summary Database information  - $sDB"); 
cRender::button("back to all databases", "alldb.php",false);
cRender::button("Details for $sDB", "dbdetail.php?".cRender::DB_QS."=$sDB",false);

//####################################################################
cChart::$width=cRender::CHART_WIDTH_LARGE;
?>
	<table class="maintable"><tr><td>
	<?php	
		$sMetric = cAppDynMetric::databaseTimeSpent($sDB);
		cChart::add("Time spent in Database", $sMetric, cAppDynCore::DATABASE_APPLICATION, 200);

		$sMetric = cAppDynMetric::databaseCalls($sDB);
		cChart::add("Database Calls", $sMetric, cAppDynCore::DATABASE_APPLICATION, 200);

		$sMetric = cAppDynMetric::databaseConnections($sDB);
		cChart::add("Database Connections", $sMetric, cAppDynCore::DATABASE_APPLICATION, 200);

	?>
	</td></tr></table>
<?php
	cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

	cRender::html_footer();
?>
