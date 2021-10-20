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
//TODO make asynchronous - separate calls for machine/db/app agents
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";


cRenderHtml::header("All Agent Versions");
cRender::force_login();
const TIER_WIDTH=150;
const ID_WIDTH=30;
const NAME_WIDTH=150;
const AGENT_WIDTH=150;



//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
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
function epoch_to_date($psEpoch){
	$sDate = date("d M Y",$psEpoch/1000);
	return $sDate;
}

//********************************************************************
function count_agents($paAgents){
	$aCount = [];
	foreach ($paAgents as $oAgent){
	
		$sRaw = null;
		if (property_exists($oAgent,"agentDetails"))
			if (property_exists($oAgent->agentDetails,"agentVersion"))
				$sRaw = $oAgent->agentDetails->agentVersion;
		
		if (!$sRaw)		$sRaw = $oAgent->version;
		
		$sVer = cADUtil::extract_agent_version($sRaw);
		@$aCount[$sVer ] ++;
	}
	ksort($aCount);
	
	echo "<div class='w3-panel w3-sand'>Agent Counts: ";
		foreach ($aCount as $sVer=>$iCount)
			echo cRenderW3::tag("$sVer: $iCount");
	echo "</div>";
}

//********************************************************************
function render_machine_agents(){
	global $gaApps;
	global $gaAppIds;
	
	try{
		$aAgents = cADRestUI::GET_machine_agents();
	}
	catch (Exception $e){
		cCommon::errorbox("Oops unable to get machine agent data from controller:<p>".$e->getMessage());		
		return;
	}
	if (cDebug::is_extra_debugging())cDebug::vardump($aAgents[0]);
	
	
	cRenderCards::card_start("<a name='m'>Machine agents</a>");
	cRenderCards::body_start();
	count_agents($aAgents);
		
		?><div class="note" id="notem">
			<div id="p0" class="mdl-progress mdl-js-progress mdl-progress__indeterminate"></div>
		</div>
		<table class="maintable" cellspacing="0" border="1" id="tblm" width="100%">
			<thead><tr class="tableheader">
				<th width="200">Application</th>
				<th width="100">Hostname</th>
				<th width="100">Version</th>
				<th width="100">Build Date</th>			
				<th width="100">Installed</th>			
				<th width="*">Runtime</th>			
			</tr></thead>
			<tbody><?php
				foreach ($aAgents as $oAgent){
					$sApp = ($oAgent->applicationIds==null?"no application":get_application_from_id($oAgent->applicationIds[0]));
					?><tr>
						<td><?=$sApp?></td>
						<td><?=cCommon::put_in_wbrs($oAgent->hostName)?></td>
						<td><?=cADUtil::extract_agent_version($oAgent->agentDetails->agentVersion)?></td>
						<td><?=parse_buildDate($oAgent->agentDetails->agentVersion)?></td>
						<td><?=epoch_to_date($oAgent->agentDetails->installTime)?></td>
						<td><?=cCommon::put_in_wbrs($oAgent->agentDetails->latestAgentRuntime)?></td>
					</tr><?php
				}
			?></tbody>
		</table>
		<script language="javascript">
			$( 
				function(){ 
					$("#tblm").tablesorter();
					$("#notem").html("click on table heading to sort");
				}
			);
		</script>
	<?php
	cRenderCards::body_end();
	cRenderCards::card_end();
}

//********************************************************************
function render_app_agents(){
	global $gaApps;
	try {
		$aAgents = cADRestUI::GET_appServer_agents();
	}
	catch (Exception $e){
		cCommon::errorbox("Oops unable to get app agent data from controller:<p>".$e->getMessage());		
		return;
	}
	if (cDebug::is_extra_debugging())cDebug::vardump($aAgents[0]);
	
	cRenderCards::card_start("<a name='a'>App agents</a>");
	cRenderCards::body_start();
		count_agents($aAgents);
		?>
		<div class="note" id="notea">
			<div id="p1" class="mdl-progress mdl-js-progress mdl-progress__indeterminate"></div>
		</div>
		<table class="maintable" cellspacing="0" border="1" id="tbla">
			<thead><tr class="tableheader">
				<th width="100">Application</th>
				<th width="100">Tier</th>
				<th width="100">Hostname</th>
				<th width="100">Node</th>
				<th width="100">Version</th>
				<th width="100">Installed</th>
				<th width="100">Runtime</th>			
			</tr></thead>
			<tbody><?php
				foreach ($aAgents as $oAgent){
					?><tr>
						<td><?=$oAgent->applicationName?></td>
						<td><?=cCommon::put_in_wbrs($oAgent->applicationComponentName)?></td>
						<td><?=cCommon::put_in_wbrs($oAgent->hostName)?></td>
						<td><?=cCommon::put_in_wbrs($oAgent->applicationComponentNodeName)?></td>
						<td><?=cADUtil::extract_agent_version($oAgent->agentDetails->agentVersion)?></td>
						<td><?=epoch_to_date($oAgent->agentDetails->installTime)?></td>
						<td><?=cCommon::put_in_wbrs($oAgent->agentDetails->latestAgentRuntime)?></td>
					</tr><?php
				}
			?></tbody>
		</table>
		<script language="javascript">
			$( 
				function(){ 
					$("#tbla").tablesorter();
					$("#notea").html("click on table heading to sort");
				}
			);
		</script>
	<?php
	cRenderCards::body_end();
	cRenderCards::card_end();
}

//********************************************************************
function render_db_agents(){
	global $gaApps;
	try{
		$aAgents = cADRestUI::GET_database_agents();
	}
	catch (Exception $e){
		cCommon::errorbox("Oops unable to get database agent data from controller:<p>".$e->getMessage());		
		return;
	}
	cRenderCards::card_start("<a name='d'>Database</a>");
	cRenderCards::body_start();
		count_agents($aAgents);
		?>
		<div class="note" id="noted">
			<div id="p2" class="mdl-progress mdl-js-progress mdl-progress__indeterminate"></div>
		</div>
		<table class="maintable" cellspacing="0" border="1" id="tbldb">
			<thead><tr class="tableheader">
				<th width="100">Name</th>
				<th width="100">Hostname</th>
				<th width="100">Version</th>
				<th width="100">Status</th>			
			</tr></thead>
			<tbody><?php
				foreach ($aAgents as $oAgent){
				?><tr>
					<td><?=$oAgent->agentName?></td>
					<td><?=cCommon::put_in_wbrs($oAgent->hostName)?></td>
					<td><?=cADUtil::extract_agent_version($oAgent->version)?></td>
					<td><?=$oAgent->status?></td>
				</tr><?php
				}
			?></tbody>
		</table>
		<script language="javascript">
			$( 
				function(){ 
					$("#tbld").tablesorter();
					$("#noted").html("click on table heading to sort");
				}
			);
		</script>
	<?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRender::button("Goto Database Agents", "alldb.php");	
	cRenderCards::action_end();
	cRenderCards::card_end();
}

//####################################################################
cRenderCards::card_start("Contents");
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
		cRender::button("latest AppDynamics versions", "../util/appdversions.php");	
		cADCommon::button(cADControllerUI::agents(), "Agent Settings");
	cRenderCards::action_end();
cRenderCards::card_end();


//####################################################################
$gaAppIds = cADUtil::get_application_ids();
render_machine_agents();
render_app_agents();
render_db_agents();
?>
<!-- ############################################################ -->
<h2><a name="o">More</a> Agents</h2>
Work in Progress

<?php
//####################################################################
cRenderHtml::footer();
?>
 