<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

$home="..";
require_once "$home/inc/common.php";

class cTierAgentCount{
	public $tier;
	public $counts;
}

//###################### DATA #############################################
$oApp = cRenderObjs::get_current_app();
$sTotals = cHeader::GET(cRender::TOTALS_QS);

//*************************************************************************
cDebug::write("getting nodes for $oApp->name");

$aNodes = $oApp->GET_Nodes();
//cDebug::vardump($aNodes);
$aTierNodes = cADAnalysis::group_nodes_by_tier($aNodes);
//cDebug::vardump($aTierNodes);
$aOut = [];
$aTotals = [];
$iTotal = 0;
foreach ($aTierNodes as $sTier=>$aNodes){
	$aCounts = cADAnalysis::count_agent_types($aNodes);
	if (!$sTotals){
		$oItem = new cTierAgentCount;
		$oItem->tier = $sTier;
		$oItem->counts = $aCounts;
		$aOut[] = $oItem; 
	}
	
	//- - - -  add to totals
	foreach ($aCounts as $oCount){
		cCommon::add_count_to_array($aTotals, $oCount->type, $oCount->count);
		$iTotal+=$oCount->count;
	}
}

if ($iTotal >0){
	//convert totals array into a standard array
	$aTotalsCounts = [];
	foreach ($aTotals as $sType=>$iCount){
		$oItem = new cAgentCounts;
		$oItem->type = $sType;
		$oItem->count = $iCount;
		$aTotalsCounts[] = $oItem;
	}
	
	$oItem = new cTierAgentCount;
	$oItem->tier = "Totals";
	$oItem->counts = $aTotalsCounts;
	$aOut[] = $oItem; //add to array 
}

//*************************************************************************
//* output
//*************************************************************************

cDebug::write("outputting json");
cCommon::write_json($aOut);	
?>
