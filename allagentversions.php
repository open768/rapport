<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2016 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
//####################################################################
$root=realpath(".");
$phpinc = realpath("$root/../phpinc");
$jsinc = "../jsinc";

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
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


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
function sort_by_tier($a, $b){
	return strnatcasecmp($a->tierName.$a->machineName, $b->tierName.$b->machineName);
}

//********************************************************************
function render_app_agents(){
	$aApps = cAppDyn::GET_Applications();
	?><table class="maintable" cellpadding="4"><?php	
		foreach ($aApps as $oApp){
			$sAppUrl = cHttp::build_url("appagents.php", cRender::build_app_qs($oApp->name, $oApp->id));
			$sClass = cRender::getRowClass();

			?>
			<tr class="<?=$sClass?>"><td colspan="5" align="left" valign="bottom">
				<p>
				<?php
					cRenderMenus::show_app_functions($oApp);
				?>
			</td></tr>
			<tr class="tableheader">
				<th >Tier</th>
				<th >Machine</th>
				<th >Node</th>
				<th >Machine Agent</th>
				<th >App Agent</th>
			</tr>
			<?php
			$aMachines = cAppDyn::GET_AppNodes($oApp->id);
			$aData = [];
			foreach ( $aMachines as $aNodes)
				foreach ($aNodes as $oNode)
					$aData[] = $oNode;
			uasort($aData, "sort_by_tier");

			if ( count($aData) == 0){
				?><tr class="<?=$sClass?>"><td colspan="5">NO Agents found</td></tr><?php
			}
			else
				foreach ($aData as $oNode){ ?>
					<tr class="<?=$sClass?>">
						<td ><?=$oNode->tierName?></td>
						<td ><?=$oNode->machineName?></td>
						<td ><?=$oNode->name?></td>
						<td ><?=cAppdynUtil::extract_agent_version($oNode->machineAgentVersion)?></td>
						<td ><?=cAppdynUtil::extract_agent_version($oNode->appAgentVersion)?></td>
					</tr><?php
				}
		}
	?></table><?php
}

//********************************************************************
function render_db_agents(){
	cCommon::flushprint("");
	$aAgents = cAppDynRestUI::GET_database_agents();
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
				<td><?=$oAgent->version?></td>
				<td><?=$oAgent->status?></td>
			</tr><?php
		}
	?></table><?php
}

//####################################################################
$sVersion = cAppdyn::GET_Controller_version();


?>
<h2>Contents</h2>
<ul>
	<li><a href="#1">Controller Version</a>
	<li><a href="#2">Machine and App Agentsn</a>
	<li><a href="#3">Database Agents</a>
	<li><a href="#4">Other Agents</a>
</ul>
<p>
<h2><a name="1">Controller Version</a></h2>
<?=$sVersion?>
<p>
<?php
cRender::button("latest AppDynamics Agents", "https://download.appdynamics.com/download/");	
?>

<p>
<!-- ############################################################ -->
<h2><a name="2">Machine and App Agents</h2>
<?php render_app_agents();?>

<!-- ############################################################ -->
<p>
<h2><a name="3">Database</a> Agents</h2>
<?php
render_db_agents();
cRender::button("Goto Database Agents", "alldb.php");	
?>
<!-- ############################################################ -->
<p>
<h2><a name="4">More</a> Agents</h2>
Work in Progress

<?php
//####################################################################
cRender::html_footer();
?>
