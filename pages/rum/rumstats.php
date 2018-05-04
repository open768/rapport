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
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");


//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();
$gsAppQS = cRender::get_base_app_QS();

//####################################################################
cRender::html_header("Web browser - Real user monitoring - Stats");
cRender::force_login();
$title ="$oApp->name&gtWeb Real User Monitoring Stats";
cRender::show_time_options( $title); 
cRenderMenus::show_apps_menu("Show Stats for:", "rumstats.php");
$oTimes = cRender::get_times();

$sGraphUrl = cHttp::build_url("rumgraph.php", $gsAppQS);
cRender::button("Graphs", $sGraphUrl);	

//#############################################################
function sort_metric_names($poRow1, $poRow2){
	return strnatcasecmp($poRow1->metricPath, $poRow2->metricPath);
}

$gsTABLE_ID = 0;

//*****************************************************************************
function render_table($psType, $paData){
	global $gsTABLE_ID, $gsAppQS;
	
	$gsTABLE_ID++;
	uasort ($paData, "sort_metric_names");
	?><table class="maintable" id="TBL<?=$gsTABLE_ID?>">
		<thead><tr class="tableheader">
			<th>Name</th>
			<th>Count</th>
			<th></th>
			<th>Max</th>
			<th>Average</th>
		</tr></thead>
		<tbody><?php
			$sClass= cRender::getRowClass();
			$iRows = 0;
			$sBaseQS = cHttp::build_QS($gsAppQS, cRender::RUM_TYPE_QS,$psType);
				
			foreach ($paData as $oItem){
				if ($oItem == null ) continue;
				if ($oItem->metricValues == null ) continue;
				
				$oValues = $oItem->metricValues[0];
				if ($oValues->count == 0 ) continue;
				$iRows++;
				$sImgMax = cRender::get_trans_speed_colour($oValues->max);
				$sName = cAppDynUtil::extract_RUM_name($psType, $oItem->metricPath);
				$sDetailQS = cHttp::build_QS($sBaseQS, cRender::RUM_PAGE_QS,$sName);

				?><tr class="<?=$sClass?>">
					<td align="right"><a href="rumpage.php?<?=$sDetailQS?>"><?=$sName?></a></td>
					<td align="middle"><?=$oValues->count?></td>
					<td><img src="<?=$sImgMax?>"></td>
					<td align="middle"><?=$oValues->max?></td>
					<td align="middle"><?=$oValues->value?></td>
				</tr><?php
			}
			
			if ($iRows == 0){
				?><tr class="<?=$sClass?>"><td colspan="5">Nothing found</td></tr><?php
			}
		?></tbody>
	</table>
	
	<script language="javascript">
		$( function(){ $("#TBL<?=$gsTABLE_ID?>").tablesorter();} );
	</script>

	<?php
}

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************


//#############################################################
//get the page metrics
?>
<h2>Page Requests</h2>
<?php
	$sMetricpath = cAppdynMetric::webrumPageResponseTimes(cAppdynMetric::BASE_PAGES, "*");
	$aData = cAppdynCore::GET_MetricData($oApp, $sMetricpath, $oTimes,"true",false,true);
	render_table(cAppdynMetric::BASE_PAGES, $aData);
	
// ############################################################
?>
<h2>Ajax Requests</h2>
<?php
	$sMetricpath = cAppdynMetric::webrumPageResponseTimes(cAppdynMetric::AJAX_REQ, "*");
	$aData = cAppdynCore::GET_MetricData($oApp, $sMetricpath, $oTimes,"true",false,true);
	render_table(cAppdynMetric::AJAX_REQ, $aData);

	// ############################################################
	cRender::html_footer();
?>
