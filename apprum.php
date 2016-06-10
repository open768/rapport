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
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);

$applink=cRender::getApplicationsLink();

//####################################################################
cRender::html_header("Web browser - Real user monitoring");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	<script type="text/javascript" src="js/chart.php"></script>
<?php
cChart::do_header();
cChart::$width=500;
cChart::$json_data_fn = "chart_getUrl";
cChart::$json_callback_fn = "chart_jsonCallBack";
cChart::$csv_url = "rest/getMetric.php";
cChart::$zoom_url = "metriczoom.php";
cChart::$save_fn = "save_fave_chart";

cChart::$compare_url = "compare.php";
cChart::$metric_qs = cRender::METRIC_QS;
cChart::$title_qs = cRender::TITLE_QS;
cChart::$app_qs = cRender::APP_QS;

$title ="$applink&gt;$app&gtWeb Real User Monitoring";
cRender::show_time_options( $title); 

cRender::show_apps_menu("Browser Stats", "apprum.php");
cRender::button("All Browser RUM", "all.php?".cRender::METRIC_TYPE_QS."=".cRender::METRIC_TYPE_RUMCALLS, false);
cRender::appdButton(cAppDynControllerUI::webrum($aid));

//####################################################################
?>
	<table class="maintable"><tr>
		<td>
		<?php
			$sMetricUrl=cAppDynMetric::appResponseTimes();
			cChart::add("Overall Application response time", $sMetricUrl, $app);
		?>
		</td>
		<td>
		<?php
			
			$sMetricUrl=cAppDynMetric::appCallsPerMin();
			cChart::add("Overall Application Calls per min", $sMetricUrl, $app);
		?>
		</td>
	</tr></table>
	<p>
<?php
cChart::$width=1000;
$class=cRender::getRowClass();
?>
	<table class="maintable">
		<tr><td class="<?=$class?>">
			<?php
				cRender::button("Page Request Details", "rumdetails.php?".cRender::APP_QS."=$app&".cRender::APP_ID_QS."=$aid&".cRender::RUM_DETAILS_QS."=".cRender::RUM_DETAILS_ACTIVITY);
				$class=cRender::getRowClass();
				$sMetricUrl=cAppDynMetric::webrumCallsPerMin();
				cChart::add("Page requests per minute", $sMetricUrl, $app);
			?>
		</td></tr>
		<tr><td class="<?=$class?>">
			<?php
				cRender::button("Page Response Details", "rumdetails.php?".cRender::APP_QS."=$app&".cRender::APP_ID_QS."=$aid&".cRender::RUM_DETAILS_QS."=".cRender::RUM_DETAILS_RESPONSE);
				$class=cRender::getRowClass();
				$sMetricUrl=cAppDynMetric::webrumResponseTimes();
				cChart::add("Page response time", $sMetricUrl, $app);
			?>
		</td></tr>
		<tr><td class="<?=$class?>">
			<?php
				$class=cRender::getRowClass();
				$sMetricUrl=cAppDynMetric::webrumTCPTime();
				cChart::add("Page connection time", $sMetricUrl, $app);
			?>
		</td></tr>
		<tr><td class="<?=$class?>">
			<?php
				$class=cRender::getRowClass();
				$sMetricUrl=cAppDynMetric::webrumServerTime();
				cChart::add("Page Server time", $sMetricUrl, $app);
			?>
		</td></tr>
		<tr><td class="<?=$class?>">
			<?php
				$class=cRender::getRowClass();
				$sMetricUrl=cAppDynMetric::webrumFirstByte();
				cChart::add("Page first byte time", $sMetricUrl, $app);
			?>
		</td></tr>
	</table>

<?php

	//-----------------------------------------------
?>

<?php
	cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

	cRender::html_footer();
?>
