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

//-----------------------------------------------
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$gsTierQS = cRenderQS::get_base_tier_QS($oTier);

//####################################################################
$title ="$oApp->name&gt;$oTier->name&gt;Errors and Exceptions";
cRenderHtml::header("$title");
cRender::force_login();

$oTimes = cRender::get_times();

$oCred = cRenderObjs::get_AD_credentials();
if ($oCred->restricted_login == null){
	cRenderMenus::show_tier_functions();
	cRenderMenus::show_tier_menu("Change Tier", "tiererrors.php");
	
	$sGraphUrl = cHttp::build_url("tiererrorgraphs.php", $gsTierQS);
	cRender::button("Show Error Graphs", $sGraphUrl);	
	cADCommon::button(cADControllerUI::tier_errors($oApp, $oTier));
}
//#############################################################
function sort_metric_names($poRow1, $poRow2){
	return strnatcasecmp($poRow1->metricPath, $poRow2->metricPath);
}

$gsTABLE_ID = 0;

//*****************************************************************************
function render_table($paData){
	global $oTier, $gsTABLE_ID;
	
	uasort ($paData, "sort_metric_names");
	?><table class="maintable" id="TBL<?=$gsTABLE_ID?>">
		<thead><tr class="tableheader">
			<th>Name</th>
			<th>Count</th>
			<th>Average</th>
		</tr></thead>
		<tbody><?php
			$sClass= cRender::getRowClass();
			$iRows = 0;
				
			foreach ($paData as $oItem){
				if ($oItem == null ) continue;
				if ($oItem->metricValues == null ) continue;
				
				$oValues = $oItem->metricValues[0];
				if ($oValues->count == 0 ) continue;
				
				$sName = cADUtil::extract_error_name($oTier->name, $oItem->metricPath);
				
				$iRows++;

				?><tr class="<?=$sClass?>">
					<td align="left"><?=$sName?></td>
					<td align="middle"><?=$oValues->count?></td>
					<td align="middle"><?=$oValues->value?></td>
				</tr><?php
			}
			
			if ($iRows == 0){
				?><tr class="<?=$sClass?>"><td colspan="3">Nothing found</td></tr><?php
			}
		?></tbody>
	</table>
	
	<script language="javascript">
		$( function(){ $("#TBL<?=$gsTABLE_ID?>").tablesorter();} );
	</script>

	<?php
}

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************


//#############################################################
//get the page metrics
?>
<h2>Errors for <?=cRender::show_name(cRender::NAME_TIER,$oTier)?></h2>
<?php
	cDebug::flush();
	$sMetricpath = cADMetricPaths::Errors($oTier->name, "*");
	$aData = $oApp->GET_MetricData( $sMetricpath, $oTimes,"true",false,true);
	render_table($aData);
	cRenderHtml::footer();
?>
