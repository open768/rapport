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


//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();
$gsAppQS = cRenderQS::get_base_app_QS($oApp);

//####################################################################
cRenderHtml::header("Web browser - Real user monitoring - Errors");
cRender::force_login();
$title ="$oApp->name&gtWeb Real User Monitoring - Errors";

cRenderMenus::show_app_change_menu("Show Stats for:");
$oTimes = cRender::get_times();

$sGraphUrl = cHttp::build_url("jserrorsgraph.php", $gsAppQS);
cRender::button("Graphs", $sGraphUrl);	

//#############################################################
function sort_metric_names($poRow1, $poRow2){
	return strnatcasecmp($poRow1->metricPath, $poRow2->metricPath);
}

$gsTABLE_ID = 0;

//*****************************************************************************
function render_table($psType, $paData){
	global $gsTABLE_ID, $gsAppQS;
	global $home;
	
	$gsTABLE_ID++;
	uasort ($paData, "sort_metric_names");
	?><table class="maintable" id="TBL<?=$gsTABLE_ID?>">
		<thead><tr class="tableheader">
			<th>Name</th>
			<th>Count</th>
			<th>Max</th>
			<th>Average</th>
		</tr></thead>
		<tbody><?php
			$sClass= cRender::getRowClass();
			$iRows = 0;
			$sBaseQS = cHttp::build_QS($gsAppQS, cRenderQS::RUM_TYPE_QS,$psType);
				
			foreach ($paData as $oItem){
				if ($oItem == null ) continue;
				if ($oItem->metricValues == null ) continue;
				
				$oValues = $oItem->metricValues[0];
				if ($oValues->count == 0 ) continue;
				$iRows++;
				$sName = cADUtil::extract_RUM_name($psType, $oItem->metricPath);
				$sRumId = cADUtil::extract_RUM_id($psType, $oItem->metricName);
				$sDetailQS = cHttp::build_QS($sBaseQS, cRenderQS::RUM_PAGE_QS,$sName);
				$sDetailQS = cHttp::build_QS($sDetailQS, cRenderQS::RUM_PAGE_ID_QS,$sRumId);

				?><tr class="<?=$sClass?>">
					<td align="right"><a href="rumpage.php?<?=$sDetailQS?>"><?=$sName?></a></td>
					<td align="middle"><?=$oValues->count?></td>
					<td align="middle"><?=$oValues->max?></td>
					<td align="middle"><?=$oValues->value?></td>
				</tr><?php
			}
			
			if ($iRows == 0){
				?><tr class="<?=$sClass?>"><td colspan="5">Nothing found</td></tr><?php
			}
		?></tbody>
	</table>
	
	<script>
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
<h2>Pages With Javascript Errors</h2>
<?php
	$sMetricpath = cADWebRumMetricPaths::PageJavaScriptErrors(cADMetricPaths::BASE_PAGES, "*");
	$aData = $oApp->GET_MetricData( $sMetricpath, $oTimes,true,false,true);
	render_table(cADMetricPaths::BASE_PAGES, $aData);
	
	// ############################################################
	cRenderHtml::footer();
?>
