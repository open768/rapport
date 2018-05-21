<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
//####################################################################
require_once("../../inc/root.php");
cRoot::set_root("../..");

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/http.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################

require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");


cRender::html_header("All Agent Versions");
cRender::force_login();
const TIER_WIDTH=150;
const ID_WIDTH=30;
const NAME_WIDTH=150;
const AGENT_WIDTH=150;


//####################################################################
cRender::show_top_banner("All AgentVersions"); 

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}


//####################################################################
function get_application_ids(){
	$aApps = cAppDynController::GET_Applications();
	$aOutput = [];
	foreach ($aApps as $oApp){
		$aOutput[ $oApp->id] = $oApp->name;
	}
	return $aOutput;
}

//********************************************************************
function get_application_from_id($psID){
	global $gaAppIds;
	if (array_key_exists($psID, $gaAppIds))
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
		$aAgents = cAppDynRestUI::GET_machine_agents();
	}
	catch (Exception $e){
		cRender::errorbox("Oops unable to get machine agent data from controller:<p>".$e->getMessage());		
		return;
	}
	
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
			?><tr class="<?=$sClass?>">
				<td><?=get_application_from_id($oAgent->applicationIds[0]) ?></td>
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
		$aAgents = cAppDynRestUI::GET_appServer_agents();
	}
	catch (Exception $e){
		cRender::errorbox("Oops unable to get app agent data from controller:<p>".$e->getMessage());		
		return;
	}
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
		$aAgents = cAppDynRestUI::GET_database_agents();
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
$gaAppIds = get_application_ids();

cRender::button("Back to Agents", "allagents.php");	
cRender::button("AppDynamics Downloads", "https://download.appdynamics.com/download/");	
cRender::button("latest AppDynamics versions", "appdversions.php");	
cRender::appdButton(cAppDynControllerUI::agents(), "Agent Settings");

?>
<h2>Contents</h2>
<ul>
	<li><a href="#c">Controller Version</a>
	<li><a href="#a">Machine Agents</a>
	<li><a href="#a">App Agents</a>
	<li><a href="#d">Database Agents</a>
	<li><a href="#o">Other Agents</a>
</ul>

<p>
<h2><a name="c">Controller Version</a></h2>
<?=cAppDynController::GET_Controller_version();?>
<p>
<?php
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
cRender::html_footer();
?>
 