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


//###################### DATA #############################################
$oTier = cRenderObjs::get_current_tier();

//*************************************************************************
$oTimes = new cADTimes();
$oTimes->set_duration(60); //last HOUR  //TBD get node retention period from controller - Thanks Ala

class cNode{
	public $name;
	public $id;
}

class cOutput{
	public $status;
	public $node_count = 0;
	public $nodes = [];
}

//*************************************************************************
$oOutput = new cOutput;

cDebug::extra_debug("Checking tier: $oTier->name");
$aNodeNames = [];

//*************************************************************************
//get agent count
$aAvailData = $oTier->GET_All_App_Agent_availability($oTimes, "*"); //get all nodes in the tier
$oOutput->node_count = count($aAvailData);
unset($aAvailData);

//*************************************************************************
//get agent availability
$aInactive = $oTier->GET_Inactive_App_Agents($oTimes); //get availability for all nodes in the tier
$iCount = count($aInactive);
if ($iCount == 0)
	$oOutput->status = "no inactive app agents found";
else{
	$oOutput->status = " $iCount inactive app agents found";
	$aAgents = [];
	foreach ($aInactive as $oAgent){
		$oNode = new cNode;
		$oNode->name = $oAgent->name;
		$oNode->id = $oAgent->id;
		$aAgents[] = $oNode;
	}
	$oOutput->nodes = $aAgents ;
}
	

//*************************************************************************
//* output
//*************************************************************************

cDebug::write("outputting json");
cCommon::write_json($oOutput);	
return;
?>
