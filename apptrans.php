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
cRender::html_header("Transactions");
cRender::force_login();

// huge time limit as this takes a long time//display the results
set_time_limit(200); 

//get passed in values
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$oTime= cRender::get_times();

// show time options
$gsAppQS = cRender::get_base_app_qs();
cRender::show_time_options("Business Transactions - $app"); 

?><h2>transaction counts and avg response times </h2><?php
cRender::show_apps_menu("Transactions", "apptrans.php");

// common function
function render_tier_transactions($psTier, $psTierID, $paTrans){	
	global $app, $oTime;

	?><table border=1 cellspacing=0>
		<tr>
			<th width=700>transaction</th>
			<th width=50>&nbsp;</th>
			<th width=90>max</th>
			<th width=90>avg</th>
			<th width=90>calls</th>
		</tr><?php
		$sBaseUrl = cHttp::build_url("transdetails.php", cRender::get_base_app_qs());
		$sBaseUrl = cHttp::build_url($sBaseUrl, cRender::TIER_QS, $psTier );
		$sBaseUrl = cHttp::build_url($sBaseUrl, cRender::TIER_ID_QS, $psTierID );
		$iCount = 0;
		
		foreach ($paTrans as $oTrans){
			$sLink = cHttp::build_url($sBaseUrl,cRender::TRANS_QS,$oTrans->name);
			$sLink = cHttp::build_url($sLink,cRender::TRANS_ID_QS,$oTrans->id);
			
			cCommon::flushprint("<!-- $oTrans->name -->");
			
			$oStats = cAppdyn::GET_TransResponse($app, $psTier, $oTrans->name, $oTime, "true");
			if (!$oStats )	continue;
			$oStats = cAppdynUtil::Analyse_Metrics($oStats);
			
			if ($oStats->count == 0)	continue;
			$iCount ++;
			
			$img = cRender::get_trans_speed_colour($oStats->max);
			
			?><tr>
				<td><a href="<?=$sLink?>"><?=$oTrans->name?></a></td>
				<td><img src="<?=$img?>" align=middle></td>
				<td align="right"><?=$oStats->max?> ms</td>
				<td align="right"><?=$oStats->avg?> ms</td>
				<td align=middle><?=$oStats->count?></td>
			</tr><?php
			
			cCommon::flushprint("");
		}
		
		if ($iCount == 0){
			?><tr><td colspan="5" align="left">No Transactions with Data found</td></tr><?php
			cCommon::flushprint("");
		}
	?></table>
	<?php
}

// work through each tier
$aTierIDsWithData = [];
$aTiers =cAppdyn::GET_Tiers($app);
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
			render_tier_transactions($oTier->name, $oTier->id, $aTrans);
		?></div><?php
	}
?></div>

<?php
cRender::html_footer();

