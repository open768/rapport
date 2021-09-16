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
//# TODO make asynchronous as it can take a long time to load
//####################################################################

//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";



//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();

//####################################################################
$title ="$oApp->name Application Errors and Exceptions";
cRenderHtml::header("$title");
cRender::force_login();

$oTimes = cRender::get_times();

$oCred = cRenderObjs::get_appd_credentials();
if ($oCred->restricted_login == null){
	cRenderCards::card_start();
	cRenderCards::action_start();
		cRenderMenus::show_app_functions($oApp);
	cRenderCards::action_end();
	cRenderCards::card_end();		
}
//#############################################################
function sort_metric_names($poRow1, $poRow2){
	return strnatcasecmp($poRow1->metricPath, $poRow2->metricPath);
}

$gsTABLE_ID = 0;

//*****************************************************************************
function render_tier_errors($poTier){
	global $oApp, $oTimes, $home, $gsTABLE_ID;
	
	?><hr><?php
	$sMetricpath = cADMetric::Errors($poTier->name, "*");
	$aData = $oApp->GET_MetricData($sMetricpath, $oTimes,"true",false,true);
			
	$iRows = 0;
	foreach ($aData as $oItem){
			if ($oItem == null ) continue;
			if ($oItem->metricValues == null ) continue;
		
			$oValues = $oItem->metricValues[0];
			if ($oValues->count == 0 ) continue;
		
		$iRows++;
	}
	
	if ($iRows == 0){
		cRenderCards::card_start($poTier->name);
		cRenderCards::body_start();
			cRender::messagebox("Nothing found");
		cRenderCards::body_end();
		cRenderCards::action_start();
			cRenderMenus::show_tier_functions($poTier);
			cRender::appdButton(cADControllerUI::tier_errors($oApp, $poTier));
		cRenderCards::action_end();
		cRenderCards::card_end();		
		return;
	}
	
	//----------ok go ahead and render---------------------------
	uasort ($aData, "sort_metric_names");


	$tierQS = cRenderQS::get_base_tier_QS( $poTier);
	
	cRenderCards::card_start($poTier->name);
	cRenderCards::body_start();
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
					
					$sName = cADUtil::extract_error_name($poTier->name, $oItem->metricPath);
					
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
		</script><?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRenderMenus::show_tier_functions($poTier);
		$sGraphUrl = cHttp::build_url("../tier/tiererrorgraphs.php", $tierQS);
		cRender::button("Show Error Graphs", $sGraphUrl);	
		cRender::appdButton(cADControllerUI::tier_errors($oApp, $poTier));
	cRenderCards::action_end();
	cRenderCards::card_end();

	if ($iRows == 0) cRender::messagebox("Nothing found for: $poTier->name");
	$gsTABLE_ID++;
	cDebug::flush();
}

//********************************************************************
if (cAD::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************


//#############################################################
//get the page metrics
$aResponse =$oApp->GET_Tiers();
if ( count($aResponse) == 0)
	cRender::messagebox("Nothing found");
else
	foreach ( $aResponse as $oTier)
		render_tier_errors($oTier);

cRenderHtml::footer();
?>
