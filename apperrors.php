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
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


//-----------------------------------------------
$oApp = cRender::get_current_app();

//####################################################################
$title ="$oApp->name Application Errors and Exceptions";
cRender::html_header("$title");
cRender::force_login();
cRender::show_time_options( $title); 
$oTimes = cRender::get_times();

$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null){
	cRenderMenus::show_app_functions($oApp);
}
//#############################################################
function sort_metric_names($poRow1, $poRow2){
	return strnatcasecmp($poRow1->metricPath, $poRow2->metricPath);
}

$gsTABLE_ID = 0;

//*****************************************************************************
function render_tier($poTier){
	global $oApp, $oTimes, $gsTABLE_ID;
	
	?><h2><?=$poTier->name?></h2><?php
	$sMetricpath = cAppdynMetric::Errors($poTier->name, "*");
	$aData = cAppdynCore::GET_MetricData($oApp->name, $sMetricpath, $oTimes,"true",false,true);
	if (count($aData) == 0){
		cRender::messagebox("Nothing found");
		return;
	}
		
	uasort ($aData, "sort_metric_names");

	cRenderMenus::show_tier_functions($poTier->name, $poTier->id);
	$tierQS = cRender::build_tier_qs($oApp, $poTier);
	$sGraphUrl = cHttp::build_url("tiererrorgraphs.php", $tierQS);
	cRender::button("Show Error Graphs", $sGraphUrl);	
	cRender::appdButton(cAppDynControllerUI::tier_errors($oApp, $poTier));
	
	?><table class="maintable" id="TBL<?=$gsTABLE_ID?>" width="1024">
		<thead><tr class="tableheader">
			<th width="*">Name</th>
			<th width="50">Count</th>
			<th width="50">Average</th>
		</tr></thead>
		<tbody><?php
			$sClass= cRender::getRowClass();
			$iRows = 0;
				
			foreach ($aData as $oItem){
				if ($oItem == null ) continue;
				if ($oItem->metricValues == null ) continue;
				
				$oValues = $oItem->metricValues[0];
				if ($oValues->count == 0 ) continue;
				
				$sName = cAppdynUtil::extract_error_name($poTier->name, $oItem->metricPath);
				
				$iRows++;

				?><tr class="<?=$sClass?>">
					<td align="left"><?=$sName?></td>
					<td align="middle"><?=$oValues->count?></td>
					<td align="middle"><?=$oValues->value?></td>
				</tr><?php
			}
			
		?></tbody>
	</table>
	
		
	<script language="javascript">
		$( function(){ $("#TBL<?=$gsTABLE_ID?>").tablesorter();} );
	</script>

	<?php
	if ($iRows == 0) cRender::messagebox("Nothing found for: $poTier->name");
	$gsTABLE_ID++;
	cDebug::flush();
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
<h1>Application Errors (<?=$oApp->name?>)</h1>
<?php
$aResponse =cAppdyn::GET_Tiers($oApp);
if ( count($aResponse) == 0)
	cRender::messagebox("Nothing found");
else
	foreach ( $aResponse as $oTier)
		render_tier($oTier);

cRender::html_footer();
?>
