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

//*************************************************************************
cDebug::write("getting details");
$sType = cHeader::GET(cRender::TYPE_QS);
$aAgents = [];

class cResults{
	public $type;
	public $counts;
	public $detail = [];
}

class cAgentCount{
	public $name;
	public $count;
}

class cAgentLine{
	public $app;
	public $node;
	public $type;
	public $version;
	public $hostname;
	public $runtime;
}

$gaAppIds = cADUtil::get_application_ids();

//*************************************************************
function get_application_from_id($psID){
	global $gaAppIds;
	if (isset($gaAppIds[$psID]))
		return $gaAppIds[$psID];
	elseif ($psID == "")
		return "<i>No Application</i>";
	else
		return "<i>Unknown Application: $psID</i>";
}

//*************************************************************
function parse_BuildDate($psInput){
	if ( preg_match('/Build Date ([\d-]*)/', $psInput, $aMatches))
		return $aMatches[1];
	else
		return $psInput;
	
}

//*************************************************************
function count_agents($paAgents){
	$aCount = [];
	foreach ($paAgents as $oAgent){
		$sVer = $oAgent->version;
		@$aCount[$sVer ] ++;		//hide warning with @
	}
	
	ksort($aCount, SORT_NUMERIC );
	$aOut = [];
	foreach ($aCount as $sKey=>$iCount){
		$oObj = new cAgentCount;
		$oObj->name = $sKey;
		$oObj->count = $iCount;
		$aOut[] = $oObj;
	}
	
	return $aOut;
}

//*************************************************************
function reduce_size($paAgents){
	$aOut = [];
	foreach ($paAgents as $oAgent){
		$sRaw = null;
		if (property_exists($oAgent,"agentDetails"))
			if (property_exists($oAgent->agentDetails,"agentVersion"))
				$sRaw = $oAgent->agentDetails->agentVersion;
		
		if (!$sRaw)		$sRaw = $oAgent->version;
		$sVer = cADUtil::extract_agent_version($sRaw);
		
		$oObj = new cAgentLine;
		$oObj->version = $sVer;
		$oObj->hostname = $oAgent->hostName;
		
		if (property_exists($oAgent,"agentDetails")){
			$oDetails = $oAgent->agentDetails;
			if (property_exists($oAgent, "applicationId"))
				$oObj->app = new cAdApp($oAgent->applicationName, $oAgent->applicationId);
			else
				$oObj->app = ($oAgent->applicationIds==null?"no application":get_application_from_id($oAgent->applicationIds[0]));

			if (property_exists($oAgent,"applicationComponentNodeName"))
				$oObj->node = $oAgent->applicationComponentNodeName;
			else
				$oObj->node = "unknown node";
			
			$oObj->type = $oDetails->type;
			$oObj->runtime = $oDetails->latestAgentRuntime;
		}
		$aOut[] = $oObj;
	}
	
	return $aOut;
}

//*************************************************************
switch ($sType){
	case "machine":
		$aAgents = cADRestUI::GET_machine_agents();
		break;
	case "app":
		$aAgents = cADRestUI::GET_appServer_agents();
		break;
	case "db":
		$aAgents = cADRestUI::GET_database_agents();
		break;
	default:
		cDebug::error("unknown type $sType");
}
cDebug::vardump($aAgents[0]);
$aReduced = reduce_size($aAgents);
$oOut = new cResults;
$oOut->type = $sType;
if ( ! cHeader::GET(cRender::TOTALS_QS))
	$oOut->detail = $aReduced;
$oOut->counts = count_agents($aReduced);

//*************************************************************************
//* output
//*************************************************************************

cDebug::write("outputting json");
cCommon::write_json($oOut);	
?>
