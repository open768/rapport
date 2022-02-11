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


set_time_limit(200); // huge time limit as this takes a long time

//display the results
$oApp = cRenderObjs::get_current_app();
$oTier = cRenderObjs::get_current_tier();
$gsTierQs = cRenderQS::get_base_tier_QS($oTier);


//**************************************************************************
function render_tier_ext($poApp, $poTier, $poData){
	global $LINK_SESS_KEY;
	
	if (sizeof($poData) > 0){
	
		$tierlink=cRender::getTierLink($poApp->name, $poApp->id, $poTier->name,  $poTier->id);
		
		?><table border=1 cellspacing=0>
			<tr>
				<th width=700><?=$tierlink?></th>
				<th colspan=4>Calls per min</th>
				<th rowspan=2 width=80>max response Times (ms)</th>
			</tr>
			<tr>
				<th width=700>other tier</th>
				<th width=80>max</th>
				<th width=80>min</th>
				<th width=80>avg</th>
				<th width=80>total</th>
			</tr><?php

			$sBaseQs = cRenderQS::get_base_tier_QS($poTier);
			foreach ( $poData as $oDetail){
				cDebug::write("DEBUG: ".$oDetail->name);
				$other_tier = $oDetail->name;
				$oCalls = $oDetail->calls;
				$oTimes = $oDetail->times;
				
				if ($oCalls && $oTimes && ($oTimes->max > 0)){
						$sQs = cHttp::build_qs($sBaseQs, cRenderQS::FROM_TIER_QS, $poTier->name);
						$sQs = cHttp::build_qs($sQs, cRenderQS::TO_TIER_QS, $other_tier);
						?><tr>
							<td><a href='tiertotier.php?<?=$sQs?>'><?=$other_tier?></a></td>
							<td align="middle"><?=$oCalls->max?></td>
							<td align="middle"><?=$oCalls->min?></td>
							<td align="middle"><?=$oCalls->avg?></td>
							<td align="middle"><?=$oCalls->sum?></td>
							<td align="middle" bgcolor="lightgrey"><?=$oTimes->max?></td>
						</tr><?php
				}
		}
		?></table><?php
	}
}
//####################################################################
cRenderHtml::header("External tier calls");
cRender::force_login();

$oCred = cRenderObjs::get_AD_credentials();
if ($oCred->restricted_login == null){
	cRenderMenus::show_app_functions();
	cRenderMenus::show_tier_functions();
	cRenderMenus::show_tier_menu("Change Tier to", cCommon::filename());
}


//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************
//********************************************************************
//####################################################################
cCommon::flushprint ("<br>");
$oTimes = cRender::get_times();
$oResponse =$oTier->GET_ext_details($oTimes);
cRender::button("show as graphs", "tierextgraph.php?$gsTierQs");
?><h2>External calls in <?=cRender::show_name(cRender::NAME_TIER,$oTier)?></h2><?php
render_tier_ext($oApp, $oTier, $oResponse);

cRenderHtml::footer();
?>
