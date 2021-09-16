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
require_once "$root/inc/charts.php";


cRenderHtml::header("All Agent Versions");
cRender::force_login();
const TIER_WIDTH=150;
const ID_WIDTH=30;
const NAME_WIDTH=150;
const AGENT_WIDTH=150;


//####################################################################
cRender::show_top_banner("All AgentVersions"); 

//********************************************************************
if (cAD::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRenderHtml::footer();
	exit;
}


//####################################################################

//********************************************************************
function get_application_from_id($psID){
	global $gaAppIds;
	if (isset($gaAppIds[$psID]))
		return $gaAppIds[$psID];
	elseif ($psID == "")
		return "<i>No Application</i>";
	else
		return "<i>Unknown Application: $psID</i>";
}

//********************************************************************
function parse_BuildDate($psInput){
	if ( preg_match('/Build Date ([\d-]*)/', $psInput, $aMatches))
		return $aMatches[1];
	else
		return $psInput;
	
}
//********************************************************************
function parse_version($psInput){
	if ( preg_match('/\s(v[\.\d]*)\s/', $psInput, $aMatches))
		return $aMatches[1];
	else
		return $psInput;
}

//********************************************************************
function epoch_to_date($psEpoch){
	$sDate = date("d M Y",$psEpoch/1000);
	return $sDate;
}


//********************************************************************
function render_machine_agents(){
	global $gaApps;
	global $gaAppIds;
	
	try{
		$aAgents = cAD_RestUI::GET_machine_agents();
	}
	catch (Exception $e){
		cRender::errorbox("Oops unable to get machine agent data from controller:<p>".$e->getMessage());		
		return;
	}
	if (cDebug::is_extra_debugging())cDebug::vardump($aAgents[0]);
	
	?><table class="maintable" cellpadding="4">
		<tr class="tableheader">
			<th>Application</th>
			<th>Hostname</th>
			<th>Version</th>
			<th>Build Date</th>			
			<th>Installed</th>			
			<th>Runtime</th>			
		</tr><?php
		$sClass = cRender::getRowClass();
		foreach ($aAgents as $oAgent){
			$sApp = ($oAgent->applicationIds==null?"no application":get_application_from_id($oAgent->applicationIds[0]));
			?><tr class="<?=$sClass?>">
				<td><?=$sApp?></td>
				<td><?=$oAgent->hostName?></td>
				<td><?=parse_version($oAgent->agentDetails->agentVersion)?></td>
				<td><?=parse_buildDate($oAgent->agentDetails->agentVersion)?></td>
				<td><?=epoch_to_date($oAgent->agentDetails->installTime)?></td>
				<td><?=$oAgent->agentDetails->latestAgentRuntime?></td>
			</tr><?php
		}
	?></table><?php
	cCommon::flushprint("");
}

//********************************************************************
function render_app_agents(){
	global $gaApps;
	try {
		$aAgents = cAD_RestUI::GET_appServer_agents();
	}
	catch (Exception $e){
		cRender::errorbox("Oops unable to get app agent data from controller:<p>".$e->getMessage());		
		return;
	}
	if (cDebug::is_extra_debugging())cDebug::vardump($aAgents[0]);
	
	?><table class="maintable" cellpadding="4">
		<tr class="tableheader">
			<th>Application</th>
			<th>Tier</th>
			<th>Hostname</th>
			<th>Node</th>
			<th>Version</th>
			<th>Installed</th>
			<th>Runtime</th>			
		</tr><?php
		$sClass = cRender::getRowClass();
		foreach ($aAgents as $oAgent){
			?><tr class="<?=$sClass?>">
				<td><?=$oAgent->applicationName?></td>
				<td><?=$oAgent->applicationComponentName?></td>
				<td><?=$oAgent->hostName?></td>
				<td><?=$oAgent->applicationComponentNodeName?></td>
				<td><?=parse_version($oAgent->agentDetails->agentVersion)?></td>
				<td><?=epoch_to_date($oAgent->agentDetails->installTime)?></td>
				<td><?=$oAgent->agentDetails->latestAgentRuntime?></td>
			</tr><?php
		}
	?></table><?php
	cCommon::flushprint("");
}

//********************************************************************
function render_db_agents(){
	global $gaApps;
	try{
		$aAgents = cAD_RestUI::GET_database_agents();
	}
	catch (Exception $e){
		cRender::errorbox("Oops unable to get database agent data from controller:<p>".$e->getMessage());		
		return;
	}
	?><table class="maintable" cellpadding="4">
		<tr class="tableheader">
			<th>Name</th>
			<th>Hostname</th>
			<th>Version</th>
			<th>Status</th>			
		</tr><?php
		$sClass = cRender::getRowClass();
		foreach ($aAgents as $oAgent){
			?><tr class="<?=$sClass?>">
				<td><?=$oAgent->agentName?></td>
				<td><?=$oAgent->hostName?></td>
				<td><?=parse_version($oAgent->version)?></td>
				<td><?=$oAgent->status?></td>
			</tr><?php
		}
	?></table><?php
	cCommon::flushprint("");
}

//####################################################################
$gaAppIds = cADUtil::get_application_ids();



//####################################################################
cRenderCards::card_start();
	cRenderCards::title_start();
		?><h1>Contents</h1><?php
	cRenderCards::title_end();
	cRenderCards::body_start();
		?>
		<ul>
			<li><a href="#a">Machine Agents</a>
			<li><a href="#a">App Agents</a>
			<li><a href="#d">Database Agents</a>
			<li><a href="#o">Other Agents</a>
		</ul>
		Controller Version: <?=cADController::GET_Controller_version();?>
		<?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRender::button("Back to Agents", "allagents.php");	
		cRender::button("AppDynamics Downloads", "https://download.appdynamics.com/download/");	
		cRender::button("latest AppDynamics versions", "appdversions.php");	
		cRender::appdButton(cADControllerUI::agents(), "Agent Settings");
	cRenderCards::action_end();
cRenderCards::card_end();
?>
<p>

<!-- ############################################################ -->
<h2><a name="m">Machine</h2>
<?php render_machine_agents();?>
<p>

<!-- ############################################################ -->
<h2><a name="a">Application</h2>
<?php render_app_agents();?>
<p>

<!-- ############################################################ -->
<h2><a name="d">Database</a> Agents</h2>
<?php
render_db_agents();
cRender::button("Goto Database Agents", "alldb.php");	
?>
<p>
<!-- ############################################################ -->
<h2><a name="o">More</a> Agents</h2>
Work in Progress

<?php
//####################################################################
cRenderHtml::footer();
?>
 