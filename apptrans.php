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

//####################################################################
// common functions
$giTotalTrans = 0;
function render_tier_transactions($poApp, $poTier){	
	global $giTotalTrans;
	
	$oTimes = cRender::get_times();
	

	?><table border=1 cellspacing=0 id="<?=$poTier->id?>">
		<thead><tr>
			<th width=700>transaction</th>
			<th width=50>&nbsp;</th>
			<th width=90>max (ms)</th>
			<th width=90>avg (ms)</th>
			<th width=90>calls</th>
		</tr></thead>
		<tbody><?php
		$sTierQS = cRender::build_tier_qs($poApp, $poTier);
		$sBaseUrl = cHttp::build_url("transdetails.php", $sTierQS);
		$iCount = 0;

		$sMetricpath = cAppdynMetric::transResponseTimes($poTier->name, "*");
		$aStats = cAppdynCore::GET_MetricData($poApp->name, $sMetricpath, $oTimes,"true",false,true);
		$giTotalTrans += count($aStats);
		cDebug::vardump($aStats, true);
		foreach ($aStats as $oTrans){
			$oStats =  cAppdynUtil::Analyse_Metrics($oTrans->metricValues);
			$sName = cAppdynUtil::extract_bt_name($oTrans->metricPath, $poTier->name);
			try{
				$sTrID = cAppdynUtil::extract_bt_id($oTrans->metricName);
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
						?><a href="<?=$sLink?>"><?=$sName?></a><?php
					}else
						echo $sName;
				?></td>
				<td><img src="<?=$img?>" align=middle></td>
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
	</table>
	<script language="javascript">
		$( function(){ $("#<?=$poTier->id?>").tablesorter();} );
	</script>
	<?php
}
//####################################################################
cRender::html_header("Transactions");
cRender::force_login();

//get passed in values
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$tier = cHeader::get(cRender::TIER_QS);
$tid = cHeader::get(cRender::TIER_ID_QS);
$oApp = cRender::get_current_app();
$oTier = cRender::get_current_tier();

$gsAppQS = cRender::get_base_app_qs();

//header
cRender::show_time_options("Business Transactions - $app"); 
//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************

?><h2>Transaction statistics (<?=$app?>)</h2><?php

//header
cRenderMenus::show_apps_menu("Change Application", "apptrans.php");
if (cFilter::isFiltered()){
	$sCleanAppQS = cRender::get_clean_base_app_QS();
	cRender::button("Clear Filter", "apptrans.php?$sCleanAppQS");
}
cRender::appdButton(cAppDynControllerUI::businessTransactions($aid));

//####################################################################
// work through each tier
$aTierIDsWithData = [];
$aTiers =cAppdyn::GET_Tiers($app);
?>
<div class="maintable"><?php
	$giTotalTrans = 0;
	foreach ( $aTiers as $oTier){
		//get the transaction names for the Tier
		if (cFilter::isTierFilteredOut($oTier->name)) continue;
		if ($tid && ($oTier->id != $tid)) continue;
		
		
		$aTrans = cAppdyn::GET_tier_transaction_names($app, $oTier->name);	
		if (!$aTrans) continue;
		if (count($aTrans) == 0) continue;

		//set up urls
		$sUrl = cHttp::build_url("tiertransgraph.php", $gsAppQS);
		$sUrl = cHttp::build_url($sUrl, cRender::TIER_QS, $oTier->name);
		$sUrl = cHttp::build_url($sUrl, cRender::TIER_ID_QS, $oTier->id);

		//display the transaction data
		?><h2>Transactions for <?=$oTier->name?></h2>
		<div class="<?=cRender::getRowClass()?>"><?php
			cRender::button("show transaction graphs", $sUrl);
			render_tier_transactions($oApp, $oTier);
		?></div><?php
	}
	?><div class="<?=cRender::getRowClass()?>">
			Total Transactions = <?=$giTotalTrans?>
	</div>
</div>

<?php
cRender::html_footer();

