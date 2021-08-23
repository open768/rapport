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

//####################################################################
cRenderHtml::header("One Click Checkup");
cRender::force_login();

//####################################################################
$sUsage = cHeader::get(cRender::USAGE_QS);
if (!$sUsage) $sUsage = 1;
cRender::show_top_banner("One Click Checkup"); 
?>
<h2>One Click Checkup</h2>

<?php
function output_row($pbBad, $psCaption, $psContent){
	$sClass = ($pbBad?"bad_row":"good_row");
	?><tr class="<?=$sClass?>">
		<th align='left' width='400'><?=$psCaption?>: </th>
		<td><?=$psContent?></td>
	</tr><?php
}

//####################################################################
//this needs to be asynchronous as when there are a lot of applications that page times out
$aResponse = cAppDynController::GET_Applications();
foreach ( $aResponse as $oApp){
	?><div><table width="100%">
		<tr><td colspan="2"><?php 
			cRenderMenus::show_app_functions($oApp);
		?></td></tr>
		<?php
		//************************************************************************************
		$aTrans = $oApp->GET_Transactions();
		$iCount = count($aTrans);
		$sCaption = "There are $iCount BTs.";
		$bBad = true;
		if ($iCount < 5)
			$sCaption .= " There are too few BTs - check BT detection configuration";
		elseif ($iCount >=250)
			$sCaption .= " This must be below 250. <b>Investigate configuration</b>";
		elseif ($iCount >=200)
			$sCaption .= " The number of transactions is on the high side. Above 250 will affect correlation";
		else{
			$bBad = false;
			$sCaption .= " Thats good.";
		}
		output_row($bBad, "Total number of Business Transactions in Application: $oApp->name", $sCaption);
		
		//************************************************************************************
		$aTierCount = [];
		foreach ($aTrans as $oTrans){
			$sTier = $oTrans->tierName;
			if (! isset($aTierCount[$sTier])) $aTierCount[$sTier] = 0;
			$aTierCount[$sTier] = $aTierCount[$sTier] +1;
		}
		
		foreach ($aTierCount as $sTier=>$iCount){
			$bBad = true;
			$sCaption = "there are $iCount BTs.";
			if ($iCount < 5)
				$sCaption .= " There are too few BTs for this tier - check BT detection configuration";
			elseif ($iCount >=50)
				$sCaption .= " This must be below 50. <b>Investigate instrumentation</b>";
			elseif ($iCount >=40)
				$sCaption .= " The number of transactions is on the high side. Above 50 will affect correlation";
			else{
				$bBad = false;
				$sCaption .= " Thats good.";
			}
			output_row($bBad, "Business Transactions for Tier: '$sTier'", $sCaption);
				
		}

		//************************************************************************************
		$aBackends = $oApp->GET_Backends();
		$iCount = count($aBackends);
		$bBad = true;
		$sCaption = "There are $iCount remote services.";
		if ($iCount >=50)
			$sCaption .= " its a little on the high side";
		elseif ($iCount >=100)
			$sCaption .= " this doesnt look right, check the detection";
		else{
			$bBad = false;
			$sCaption .= " Thats looks ok.";
		}
		output_row($bBad, "Remote services used by Application: $oApp->name", $sCaption);
				
		//************************************************************************************
	?></table></div><p><?php
	if (cDebug::is_debugging()) break;
	cCommon::flushprint("");
}

cRenderHtml::footer();
?>
