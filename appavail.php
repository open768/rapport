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
cRender::html_header("App Activity");
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

//####################################################################
//get passed in values
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$duration = get_duration();

$applink=cRender::getApplicationsLink();
$topurl = cRender::getTopLink($app,$aid);

$title= "$applink&gt;$app&gt;Availability";
cRender::show_time_options($title); 
cRender::show_apps_menu("Availability", "appavail.php");

//####################################################################
//retrieve tiers
$oResponse =cAppdyn::GET_Tiers($app);

// work through each tier
?>
<h2>Availability for <?=$app?></h2>
<table class='maintable'>
	<?php
	foreach ( $oResponse as $oItem){
		$tier = $oItem->name;
		$tid= $oItem->id;
		$sServerMetricUrl=cAppDynMetric::InfrastructureMachineAvailability($tier);
		$sAgentMetricUrl=cAppDynMetric::InfrastructureAgentAvailability($tier);
		
		echo "<tr><td class='".cRender::getRowClass()."'>";
			cRender::button($tier, cRender::getTierLinkUrl($app,$aid,$tier,$tid));
			cChart::add("'$tier': Server availability", $sServerMetricUrl, $app);	
			cChart::add("'$tier': Agent availability", $sAgentMetricUrl, $app);	
		echo "</td></tr>";
	}
	?>
</table>
<?php
cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

cRender::html_footer();
?>
