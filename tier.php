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

set_time_limit(200); // huge time limit as this takes a long time

//display the results
$app = cHeader::get(cRender::APP_QS);
$tier = cHeader::get(cRender::TIER_QS);

$SHOW_PROGRESS=true;
$duration = get_duration();
$sAppQs = cRender::get_base_app_QS();
$sTierQs = cRender::get_base_tier_QS();

//####################################################################
cRender::html_header("tier $tier");
?>
	<script type="text/javascript" src="js/remote.js"></script>
	<script type="text/javascript" src="js/chart.php"></script>
<?php
cRender::force_login();
cRender::show_time_options("$app&gt;$tier"); 

cChart::do_header();
cChart::$width=940;
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
$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null){
	$aNodes = cAppDyn::GET_TierInfraNodes($app,$tier);	
	$sInfraUrl = cHttp::build_url("tierinfrstats.php",$sTierQs);

	cRender::show_tier_functions();
	?>
		<select id="menuNodes">
			<option selected disabled>Individual Servers</option>
			<?php
				foreach ($aNodes as $oNode){
					?><option value="<?=cHttp::build_url($sInfraUrl, cRender::NODE_QS, $oNode->name)?>"><?=$oNode->name?></option><?php
				}
			?>
		</select>
		<script language="javascript">
		$(  
			function(){
				$("#menuNodes").selectmenu({change:common_onListChange});
			}  
		);
		</script>
	<?php 
}

//####################################################################
?>

<h2>Overall Stats for (<?=$app?>) Application</h2>
<table class="maintable">
	<tr><td class="<?=cRender::getRowClass()?>"><?php
		$sMetricUrl=cAppDynMetric::appResponseTimes();
		cChart::add("Overall response time in ms ($app) application", $sMetricUrl, $app);
	?></td></tr>
	<tr><td class="<?=cRender::getRowClass()?>"><?php
		$sMetricUrl=cAppDynMetric::appCallsPerMin();
		cChart::add("Overall Calls per min ($app) application", $sMetricUrl, $app);
	?></td></tr>
</table>

<h2>(<?=$tier?>) Tier</h2>
<table class="maintable">
	<tr><td class="<?=cRender::getRowClass()?>"><?php
		$sMetricUrl=cAppDynMetric::tierCallsPerMin($tier);
		cChart::add("Calls per min for ($tier) tier", $sMetricUrl, $app);
	?></td></tr>
	<tr><td class="<?=cRender::getRowClass()?>"><?php
		$sMetricUrl=cAppDynMetric::tierResponseTimes($tier);
		cChart::add("Response times in ms for ($tier) tier", $sMetricUrl, $app);
	?></td></tr>
	<tr><td class="<?=cRender::getRowClass()?>"><?php
		$sMetricUrl=cAppDynMetric::InfrastructureMachineAvailability($tier);
		cChart::add("Server Availability", $sMetricUrl, $app);
	?></td></tr>
	<tr><td class="<?=cRender::getRowClass()?>"><?php
		$sMetricUrl=cAppDynMetric::InfrastructureAgentAvailability($tier);
		cChart::add("Agent Availability", $sMetricUrl, $app);
	?></td></tr>
</table>
<?php
//####################################################################
cChart::do_footer("chart_getUrl", "chart_jsonCallBack");
cRender::html_footer();
?>
