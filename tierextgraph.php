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


//####################################################################
set_time_limit(200); // huge time limit as this takes a long time

//display the results
$app = cHeader::get(cRender::APP_QS);
$tier = cHeader::get(cRender::TIER_QS);
$tid = cHeader::get(cRender::TIER_ID_QS);

$gsAppQs = cRender::get_base_app_QS();
$gsTierQs = cRender::get_base_tier_QS();

//################### CHART HEADER ########################################
cRender::html_header("External tier calls");
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

//###################### DATA ########################################
$title = "$app&gt;$tier&gt;External Calls";

cRender::show_time_options($title); 
$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null)	cRender::show_tier_functions();

//************* basic information about the tier *********************
?>
<h2>External calls made from (<?=$tier?>) tier</h2>
<h3>Overall Stats for tier</h3>
<table class="maintable">
	<tr><td class="<?=cRender::getRowClass()?>"><?php
		$sMetricUrl=cAppDynMetric::tierCallsPerMin($tier);
		cChart::add("Overall Calls per min for ($tier) tier", $sMetricUrl, $app);
	?></td></tr>
	<tr><td class="<?=cRender::getRowClass()?>"><?php
		$sMetricUrl=cAppDynMetric::tierResponseTimes($tier);
		cChart::add("Overall  response times in ms for ($tier) tier", $sMetricUrl, $app);
	?></td></tr>
</table>
<p>
<!-- ***************************************************** -->
<?php
	$oResponse = cAppdyn::GET_tier_ExtCalls_Metric_heirarchy($app, $tier);
	$linkUrl = cHttp::build_url("tiertotier.php", $gsAppQs);
	$linkUrl = cHttp::build_url($linkUrl, cRender::FROM_TIER_QS, $tier);
	$linkUrl = cHttp::build_url($linkUrl, cRender::TIER_ID_QS, $tid);
?>
<h3>External calls</h3>
<table class="maintable"><?php
	foreach ($oResponse as $oDetail){
		$sTierTo = $oDetail->name;
		$sMetricUrl = cAppDynMetric::tierExtResponseTimes($tier, $sTierTo);
		?><tr><td class="<?=cRender::getRowClass()?>"><?php
			cRender::button("details for call to ($sTierTo)", cHttp::build_url($linkUrl, cRender::TO_TIER_QS, $sTierTo));
			cChart::add("response time in ms to ($sTierTo)", $sMetricUrl, $app);
		?></td></tr><?php
	}
?></table>
<p>
<?php

//---------------------------------------------------------------
cRender::button("$tier transactions", cHttp::build_url("tiertrans.php", $gsTierQS));
cRender::button("summary", cHttp::build_url("tierextcalls.php", $gsTierQS));

//################ CHART
cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

cRender::html_footer();
?>