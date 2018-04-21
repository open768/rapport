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
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");

//####################################################################
cRender::html_header("Activity Heat Map");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
//choose a default duration

$SHOW_PROGRESS=false;
set_time_limit(200); 

//get passed in values
$oApp = cRenderObjs::get_current_app();
$tier = cHeader::get(cRender::TIER_QS);




$title= "$oApp->name&gt;Activity&gt;heatmap";
cRender::show_time_options($title); 
cRenderMenus::show_apps_menu("heatmap", "appheatmap.php");

// get the data from 
if ($tier === null)
	$metric = cAppDynMetric::appResponseTimes();
else
	$metric = cAppDynMetric::tierCallsPerMin($tier);

?>
<h2>Heatmap for <?=cRender::show_app_name($oApp)?></h2>
<?php
//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************

$oTime= cRender::get_times();
$oResponse = cAppdynCore::GET_MetricData($oApp->id,$metric, $oTime);
$aHeatData = cAppdynUtil::Analyse_heatmap( $oResponse);
cRender::render_Heatmap($aHeatData["days"], "HeatMap for Days of Week", "hour", "day");
cRender::render_Heatmap($aHeatData["hours"], "HeatMap for Hours", "hour", "min");
?>

<?php
cRender::html_footer();
?>

