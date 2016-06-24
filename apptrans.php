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

//####################################################################
// common functions
function render_tier_transactions($psApp, $psTier, $psTierID, $paTrans, $poTimes){	

	?><table border=1 cellspacing=0 id="<?=$psTierID?>">
		<thead><tr>
			<th width=700>transaction</th>
			<th width=50>&nbsp;</th>
			<th width=90>max (ms)</th>
			<th width=90>avg (ms)</th>
			<th width=90>calls</th>
		</tr></thead>
		<tbody><?php
		$sBaseUrl = cHttp::build_url("transdetails.php", cRender::get_base_app_qs());
		$sBaseUrl = cHttp::build_url($sBaseUrl, cRender::TIER_QS, $psTier );
		$sBaseUrl = cHttp::build_url($sBaseUrl, cRender::TIER_ID_QS, $psTierID );
		$iCount = 0;

		$aStats = cAppdyn::GET_TransResponse($psApp, $psTier, "*", $poTimes, "true");
		foreach ($aStats as $oTrans){
			$oStats =  cAppdynUtil::Analyse_Metrics($oTrans->metricValues);
			$sName = cAppdynUtil::extract_bt_name($oTrans->metricPath, $psTier);
			
			$sLink = cHttp::build_url($sBaseUrl,cRender::TRANS_QS,$sName);
			
			if ($oStats->count == 0)	continue;
			$iCount ++;
			
			$img = cRender::get_trans_speed_colour($oStats->max);
			
			?><tr>
				<td><a href="<?=$sLink?>"><?=$sName?></a></td>
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
		?></tbody>
	</table>
	<script language="javascript">
		$( function(){ $("#<?=$psTierID?>").tablesorter();} );
	</script>
	<?php
}
//####################################################################
cRender::html_header("Transactions");
cRender::force_login();

//get passed in values
$app = cHeader::get(cRender::APP_QS);
$gsAppQS = cRender::get_base_app_qs();

//header
cRender::show_time_options("Business Transactions - $app"); 

?><h2>Transaction statistics (<?=$app?>)</h2><?php

//header
cRender::show_apps_menu("Change Application", "apptrans.php");
if (cFilter::isFiltered()){
	$sCleanAppQS = cRender::get_clean_base_app_QS();
	cRender::button("Clear Filter", "apptrans.php?$sCleanAppQS");
}

$aTiers =cAppdyn::GET_Tiers($app);

// work through each tier
$aTierIDsWithData = [];
$aTiers =cAppdyn::GET_Tiers($app);
$oTimes = cRender::get_times();
?>
<div class="maintable"><?php
	foreach ( $aTiers as $oTier){
		//get the transaction names for the Tier
		if (cFilter::isTierFilteredOut($oTier->name)) continue;
		
		$aTrans = cAppdyn::GET_tier_transaction_names($app, $oTier->name);	
		cCommon::flushprint("<!-- $oTier->name -->");
		
		if (!$aTrans) continue;
		if (count($aTrans) == 0) continue;

		//set up urls
		$sUrl = cHttp::build_url("tiertransgraph.php", $gsAppQS);
		$sUrl = cHttp::build_url($sUrl, cRender::TIER_QS, $oTier->name);
		$sUrl = cHttp::build_url($sUrl, cRender::TIER_ID_QS, $oTier->id);

		//display the transaction data
		?><div class="<?=cRender::getRowClass()?>"><?php
			cRender::button($oTier->name, $sUrl);
			render_tier_transactions($app, $oTier->name, $oTier->id, $aTrans, $oTimes);
		?></div><?php
	}
?></div>

<?php
cRender::html_footer();

