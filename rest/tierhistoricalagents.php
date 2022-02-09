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
$oTimes->set_duration(60*24); //last day

class cNode{
	public $name, $id;
}

class cOutput{
	public $status;
	public $node_count = 0;
	public $version_counts = [];
	public $nodes = [];
}

//*************************************************************************
$oOutput = new cOutput;

cDebug::extra_debug("Checking tier: $oTier->name");
$sStatus = "no status";
$iCount = 0;

//*************************************************************************
//get the nodes, will need to double check the metrics
$aNodes = $oTier->GET_nodes(); //get availability for all nodes in the tier

//*************************************************************************
//get agent availability
$aAvailData = $oTier->GET_All_App_Agent_availability($oTimes, "*"); //get availability for all nodes in the tier
if (!cArrayUtil::array_is_empty($aAvailData)){
	cDebug::extra_debug("app agents found for: $oTier->name = ". count($aAvailData));
	//cDebug::vardump($aData);
	$oOutput->node_count = count($aAvailData);
	foreach ($aAvailData as $oItem) //loop through nodes
		if (count($oItem->metricValues) > 0)
			if ($oItem->metricValues[0]->sum == 0){						//only historical if the sum is 0
				$sNode = cAdUtil::extract_node_name($oItem->metricPath);
				$sNodeID = cAdUtil::get_node_id($oTier->app, $sNode);
				$oNode = new cNode;
				$oNode->name = $sNode;
				$oNode->id = $sNodeID;
				$oOutput->nodes[] = $oNode;
				$iCount ++;
			}
	
	if ($iCount == 0)
		$oOutput->status = "no inactive app agents found";
	else
		$oOutput->status = "inactive app agents found";
	}
	
	//cross reference with nodes  showing as no metrics uploaded
	//TBD
	
	//check list of nodes for tier against this list of nodes from above to see if nodes are missing
	//TBD
}else{
	// no app agents found, but you would expect that each node has at least one app agent
	cDebug::extra_debug("no app agents data found");
	if (cArrayUtil::array_is_empty($aNodes))
		$oOutput->status = "no nodes found for tier";
	else{
		$oOutput->status = "no app agents data found - using list of nodes";
		foreach ($aNodes as $oItem){
			$oNode = new cNode;
			$oNode->name = $oItem->name;
			$oNode->id = cAdUtil::get_node_id($oTier->app, $oItem->name);
			$oOutput->nodes[] = $oNode;
		}
	}
}


//*************************************************************************
//* output
//*************************************************************************

cDebug::write("outputting json");
cCommon::write_json($oOutput);	
return;
?>
