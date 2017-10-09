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

$SHOW_PROGRESS=false;
set_time_limit(200); 

//####################################################################
cRender::html_header("Information Points");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();
cChart::$json_data_fn = "chart_getUrl";
cChart::$json_callback_fn = "chart_jsonCallBack";
cChart::$csv_url = "rest/getMetric.php";
cChart::$zoom_url = "metriczoom.php";
cChart::$save_fn = "save_fave_chart";
cChart::$compare_url = "compare.php";

cChart::$metric_qs = cRender::METRIC_QS;
cChart::$title_qs = cRender::TITLE_QS;
cChart::$app_qs = cRender::APP_QS;
cChart::$width = cRender::CHART_WIDTH_LETTERBOX/4;

//####################################################################
//get passed in values
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);


$title= "$app&gt;Information Points";
cRender::show_time_options($title); 
cRenderMenus::show_apps_menu("Information Points for", "appinfo.php");

//####################################################################
//retrieve tiers
$oTimes = cRender::get_times();
$aInfoPoints = cAppdyn::GET_AppInfoPoints($app, $oTimes);
cDebug::vardump($aInfoPoints, true);
if (count($aInfoPoints) == 0)
	cRender::messagebox("No information points found");
else{
	?><table class="maintable">
		<tr>
			<th>Information Point</th>
			<th>Activity</th>
			<th>Response Times in ms</th>
			<th>Errors per minute</th>
		</tr>
		<?php
			foreach ($aInfoPoints as $oInfoPoint){
				?><tr class="<?=cRender::getRowClass()?>">
					<td><?=$oInfoPoint->name?></td>
					<td><?php	
						$sMetricUrl = cAppdynMetric::infoPointCallsPerMin($oInfoPoint->name);
						cChart::add("Calls", $sMetricUrl, $app, cRender::CHART_HEIGHT_SMALL);
					?></td>
					<td><?php	
						$sMetricUrl = cAppdynMetric::infoPointResponseTimes($oInfoPoint->name);
						cChart::add("Response", $sMetricUrl, $app, cRender::CHART_HEIGHT_SMALL);
					?></td>
					<td><?php	
						$sMetricUrl = cAppdynMetric::infoPointErrorsPerMin($oInfoPoint->name);
						cChart::add("Errors", $sMetricUrl, $app, cRender::CHART_HEIGHT_SMALL);
					?></td>
				</tr><?php
			}
		?>
	</table><?php 
}

//####################################################################
cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

cRender::html_footer();
?>
