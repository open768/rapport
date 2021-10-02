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
//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";

require_once("$root/inc/charts.php");

//####################################################################
// common functions
$giTotalTrans = 0;
function render_tier_transactions($poTier){	
	global $giTotalTrans, $home;
	
	$oTimes = cRender::get_times();
	$sMetricpath = cADMetric::transResponseTimes($poTier->name, "*");
	try{
	$aStats = $poTier->app->GET_MetricData($sMetricpath, $oTimes,"true",false,true);
	}catch (Exception $e){
		cCommon::errorbox("Oops unable to retrieve Transaction names: ".$e);
		return;
	}

	?><div class="<?=cRender::getRowClass()?>"><table border=1 cellspacing=0 id="<?=$poTier->id?>">
		<thead><tr>
			<th width=700>transaction</th>
			<th width=50>&nbsp;</th>
			<th width=90>max (ms)</th>
			<th width=90>avg (ms)</th>
			<th width=90>calls</th>
		</tr></thead>
		<tbody><?php
		$sTierQS = cRenderQS::get_base_tier_QS($poTier);
		$sBaseUrl = cHttp::build_url("../trans/transdetails.php", $sTierQS);
		$iCount = 0;
		
		$giTotalTrans += count($aStats);
		cDebug::vardump($aStats, true);
		foreach ($aStats as $oTrans){
			$oStats =  cADUtil::Analyse_Metrics($oTrans->metricValues);
			$sName = cADUtil::extract_bt_name($oTrans->metricPath, $poTier->name);
			try{
				$sTrID = cADUtil::extract_bt_id($oTrans->metricName);
			}
			catch (Exception $e){
				$sTrID = null;
			}
			$sLink = null;
			if ($sTrID){
				$sLink = cHttp::build_url($sBaseUrl,cRender::TRANS_QS,$sName);
				$sLink = cHttp::build_url($sLink,cRender::TRANS_ID_QS,$sTrID);
			}
			
			if ($oStats->count == 0)	continue;
			$iCount ++;
			
			$img = cRender::get_trans_speed_colour($oStats->max);
			
			?><tr>
				<td><?php
					if ($sLink){
						?><a href="<?=$sLink?>"><?=cRender::show_name(cRender::NAME_TRANS,$sName)?></a><?php
					}else
						echo $sName;
				?></td>
				<td><img src="<?=$home?>/<?=$img?>" align=middle></td>
				<td align="right"><?=$oStats->max?></td>
				<td align="right"><?=$oStats->avg?></td>
				<td align=middle><?=$oStats->count?></td>
			</tr><?php
			
			cCommon::flushprint("");
		}
		
		if ($iCount == 0){
			?><tr><td colspan="5" align="left">No Transactions with Data found</td></tr><?php
			cCommon::flushprint("");
		}
		?>
		</tbody>
		<tfoot>
			<tr><td colspan="5" align="right">
				showing <?=$iCount?> of <?=count($aStats)?> transactions
			</td></tr>
		</tfoot>
	</table></div>
	<script language="javascript">
		$( function(){ $("#<?=$poTier->id?>").tablesorter();} );
	</script>
	<?php
}
//####################################################################
//get passed in values
$tier = cHeader::get(cRender::TIER_QS);
$tid = cHeader::get(cRender::TIER_ID_QS);
$oApp = cRenderObjs::get_current_app();
$oTier = cRenderObjs::get_current_tier();
$gsAppQS = cRenderQS::get_base_app_QS($oApp);

cRenderHtml::header("Transactions for $oApp->name");
cRender::force_login();
//header

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************


cRenderCards::card_start();
cRenderCards::action_start();
	cRenderMenus::show_apps_menu("Change Application", "apptrans.php");
	if (cFilter::isFiltered()){
		$sCleanAppQS = cRenderQS::get_base_app_QS($oApp);
		cRender::button("Clear Filter", "apptrans.php?$sCleanAppQS");
	}
	cADCommon::button(cADControllerUI::businessTransactions($oApp));
	cRender::add_filter_box("select[type=admenus]","tier",".mdl-card");
cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
// work through each tier
// TODO make asynchronous
/* TODO USE restui instead of multiple calls
https://XXXX.saas.appdynamics.com/controller/restui/v1/bt/listViewDataByColumns
{"requestFilter":[APP],"searchFilters":null,"timeRangeStart":[EPIC START EG 1632168237113],"timeRangeEnd":[EPIC END] ,"columnSorts":null,"resultColumns":["NAME","BT_HEALTH","AVERAGE_RESPONSE_TIME","CALL_PER_MIN","ERRORS_PER_MIN","PERCENTAGE_ERROR","PERCENTAGE_SLOW_TRANSACTIONS","PERCENTAGE_VERY_SLOW_TRANSACTIONS","PERCENTAGE_STALLED_TRANSACTIONS","MAX_RESPONSE_TIME","TIER","TYPE"],"offset":0,"limit":-1}
*/
//
$aTierIDsWithData = [];
$aTiers =$oApp->GET_Tiers();
$giTotalTrans = 0;
foreach ( $aTiers as $oTier){
	$oTier->app = $oApp;
	if (cFilter::isTierFilteredOut($oTier)) continue;
	
	//get the transaction names for the Tier
	cRenderCards::card_start();
		cRenderCards::body_start();
			$aTrans = $oTier->GET_transaction_names();	
			if (!$aTrans) continue;
			if (count($aTrans) == 0) continue;

			//set up urls
			$sUrl = cHttp::build_url("tiertransgraph.php", $gsAppQS);
			$sUrl = cHttp::build_url($sUrl, cRender::TIER_QS, $oTier->name);
			$sUrl = cHttp::build_url($sUrl, cRender::TIER_ID_QS, $oTier->id);

			
			//display the transaction data
			render_tier_transactions($oTier);
		cRenderCards::body_end();
		cRenderCards::action_start();
			cRenderMenus::show_tier_functions($oTier);
			cRender::button("show transaction graphs", $sUrl);
		cRenderCards::action_end();
	cRenderCards::card_end();
}
//***************************************************************
cRenderCards::card_start("Totals");
cRenderCards::body_start();
	echo "Total Transactions = $giTotalTrans";
	cRenderCards::body_end();
cRenderCards::card_end();

cRenderHtml::footer();
?>
