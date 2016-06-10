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
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


$sMetricType = cHeader::get(cRender::METRIC_TYPE_QS);
switch($sMetricType){
	case cRender::METRIC_TYPE_RUMCALLS:
		$sTitle = "Web Browser Page Requests";
		$sMetric = cAppDynMetric::webrumCallsPerMin();
		break;
	case cRender::METRIC_TYPE_RUMRESPONSE:
		$sTitle = "Web Browser Page Response";
		$sMetric = cAppDynMetric::webrumResponseTimes();
		break;
	case cRender::METRIC_TYPE_RESPONSE_TIMES:
		$sTitle = "Application Response Times";
		$sMetric = cAppDynMetric::appResponseTimes();
		break;
	case cRender::METRIC_TYPE_ACTIVITY:
		$sTitle = "Application Activity";
		$sMetric = cAppDynMetric::appCallsPerMin();
		break;
	default:
		cDebug::error("unknown  metric type $sMetricType");
}

//####################################################################
cRender::html_header("All Applications - $sTitle");
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

//####################################################################
cRender::show_time_options( "All Applications - $sTitle"); 
		

//####################################################################
cChart::$width=940;
$oResponse = cAppDyn::GET_Applications();
?>
	<table class="maintable">
	<?php	
		if (count($oResponse) == 0){
			?><tr><td><h2>Nothing found</h2></td></tr><?php
		}else
			//display the results
			foreach ( $oResponse as $oApp){
				if (cFilter::isAppFilteredOut($oApp->name)) continue;
				?>
					<tr><td class="<?=cRender::getRowClass()?>">
						<?=cRender::show_app_functions($oApp->name, $oApp->id)?>
						<br>
						<?php
							cChart::add($sTitle, $sMetric, $oApp->name, 200);
						?>
					</td></tr>
				<?php
			}
	?>
	</table>
<?php
	cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

	cRender::html_footer();
?>
