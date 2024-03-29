<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";



//####################################################################
cRenderHtml::header("Activity Heat Map");
cRender::force_login();
?>
	<script src="js/remote.js"></script>
	
<?php
//choose a default duration

set_time_limit(200); 

//get passed in values
$oApp = cRenderObjs::get_current_app();
$tier = cHeader::get(cRenderQS::TIER_QS);




$title= "$oApp->name&gt;Activity&gt;heatmap";

cRenderMenus::show_app_change_menu("heatmap");

// get the data from 
if ($tier === null)
	$metric = cADAppMetricPaths::appResponseTimes();
else
	$metric = cADTierMetricPaths::tierCallsPerMin($tier);

?>
<h2>Heatmap for <?=$oApp->name?></h2>
<?php
//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

$oTime= cRender::get_times();
$oResponse = $oApp->GET_MetricData($metric, $oTime);
$aHeatData = cADAnalysis::analyse_heatmap( $oResponse);
cRender::render_Heatmap($aHeatData["days"], "HeatMap for Days of Week", "hour", "day");
cRender::render_Heatmap($aHeatData["hours"], "HeatMap for Hours", "hour", "min");
?>

<?php
cRenderHtml::footer();
?>

