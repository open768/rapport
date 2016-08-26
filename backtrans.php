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
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/pubsub/pub-sub.php");
	
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
cRender::html_header("Backend Transactions");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	<script type="text/javascript" src="js/chart.php"></script>
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

//get passed in values
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$backend = cHeader::get(cRender::BACKEND_QS);
$sAppQS = cRender::get_base_app_QS();
$sBackendQS = cHttp::build_QS($sAppQS, cRender::BACKEND_QS, $backend);


$title= "$app&gt;Backend Transactions&gt;$backend";
cRender::show_time_options($title); 
cRender::button("Back to Backends", "backends.php?$sAppQS");
cRender::button("Backend Tier Calls", "backcalls.php?$sBackendQS");
cRender::button("Backend Transaction Timings", "backtransresponse.php?$sBackendQS");
echo "<br>";

cChart::$width = cRender::CHART_WIDTH_LETTERBOX/2;
?>
<span id="progress"><?php
	$aTransactions = cAppdyn::GET_BackendCallerTransactions($app, $backend);
?></span>
<script language="javascript">
	$("#progress").hide();
</script>

<table class='maintable'><tr>
	<td><?php
		$sMetricUrl=cAppDynMetric::appCallsPerMin();
		cChart::add("Overall Calls per min ($app)", $sMetricUrl, $app, cRender::CHART_HEIGHT_LETTERBOX2);
	?></td>
	<td><?php
		$sMetricUrl=cAppDynMetric::backendCallsPerMin($backend);
		cChart::add("Overall Calls per min ($backend)", $sMetricUrl, $app, cRender::CHART_HEIGHT_LETTERBOX2);
	?></td>
</tr></table>
<p>

<table class='maintable'>
	<?php
		foreach ($aTransactions as $oItem){
			?><tr class="<?=cRender::getRowClass()?>">
				<td><?php
					$sMetric = $oItem->metric."|".cAppDynMetric::CALLS_PER_MIN;
					cChart::add($sMetric, $sMetric, $app, cRender::CHART_HEIGHT_LETTERBOX2);	
				?></td><td><?php
					$sMetric = $oItem->metric."|".cAppDynMetric::RESPONSE_TIME;
					cChart::add($sMetric, $sMetric, $app, cRender::CHART_HEIGHT_LETTERBOX2);
				?></td>
			</tr><?php
		}
	?>
</table>
<p>
<?php	
cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

cRender::html_footer();
?>
