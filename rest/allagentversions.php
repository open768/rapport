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
$sType = cHeader::GET(cRenderQS::TYPE_QS);
$oApp = null;
try{
	$oApp = cRenderObjs::get_current_app();
}catch (Exception $e){}

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


$gaAppIds = cADUtil::get_application_ids();

//*************************************************************
function get_application_from_id($psID){
	global $gaAppIds;
	if (isset($gaAppIds[$psID]))
		return $gaAppIds[$psID];
	else
		return null;
}

//*************************************************************
function parse_BuildDate($psInput){
	if ( preg_match('/Build Date ([\d-]*)/', $psInput, $aMatches))
		return $aMatches[1];
	else
		return $psInput;
	
}

//*************************************************************
function count_agent_versions($paAgents){
	$aCount = [];
	
	foreach ($paAgents as $oAgent){
		$sAgent = $oAgent->type . " - " . $oAgent->version;
		@$aCount[$sAgent ] ++;		//hide warning with @
	}
	
	ksort($aCount );
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
function count_agent_totals($paAgents){
	$aCount = [];
	foreach ($paAgents as $oAgent){
		$sVer = $oAgent->type . " - ". $oAgent->version;
		@$aCount[$sVer ] ++;		//hide warning with @
	}
	
	ksort($aCount);
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
	global $oApp;
	global $sType;
	
	$aAgents = cADAnalysis::analyse_agents($paAgents, $sType);
	$aOut = [];
	
	
	if ($oApp){
		$sLowerApp = strtolower($oApp->name);
		foreach ($aAgents as $oAgent){
			if (!$oAgent->app)
				continue;
			elseif (strtolower($oAgent->app->name) !== $sLowerApp) 
				continue;
			$aOut[] = $oAgent;
		}
	}else
		$aOut = $aAgents;
	
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
$oOut->counts = count_agent_totals($aReduced);
if ( cHeader::GET(cRenderQS::TOTALS_QS))
	$oOut->detail = count_agent_versions($aReduced);
else
	$oOut->detail = $aReduced;


//*************************************************************************
//* output
//*************************************************************************

cDebug::write("outputting json");
cCommon::write_json($oOut);	
?>
