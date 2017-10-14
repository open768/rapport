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


cRender::html_header("All Nodes");
cRender::force_login();
const TIER_WIDTH=150;
const ID_WIDTH=30;
const NAME_WIDTH=150;
const AGENT_WIDTH=150;


//####################################################################
cRender::show_top_banner("All Nodes"); 

$maApps = cAppDyn::GET_Applications();
$bShown = false;

function sort_by_tier($a, $b){
	return strnatcasecmp($a->tierName.$a->machineName, $b->tierName.$b->machineName);
}

?>
<h2>All Nodes for all Applications</h2>

<table class="maintable" width="100%">
<?php	
	foreach ($maApps as $oApp){
		$sAppUrl = cHttp::build_url("appagents.php", cRender::build_app_qs($oApp->name, $oApp->id));
		$sClass = cRender::getRowClass();

		?>
		<tr class="<?=$sClass?>"><td colspan="9" align="left" valign="bottom">
			<h3><?=cRender::button($oApp->name, $sAppUrl)?></h3>
			<!-- <a class="blue_button" href="<?=$sAppUrl?>"><?=$oApp->name?></a>-->
		</td></tr>
		<tr class="tableheader">
			<th width="<?=TIER_WIDTH?>">Tier</th>
			<th width="<?=NAME_WIDTH?>" colspan="3">Machine</th>
			<th width="<?=NAME_WIDTH?>" colspan="2" >Node</th>
			<th width="<?=AGENT_WIDTH?>">Machine Agent</th>
			<th width="<?=AGENT_WIDTH*2?>" colspan="2">App Agent</th>
		</tr>
		<?php
		$aMachines = cAppDyn::GET_AppNodes($oApp->id);
		$aData = [];
		foreach ( $aMachines as $aNodes)
			foreach ($aNodes as $oNode)
				$aData[] = $oNode;
		uasort($aData, "sort_by_tier");

		foreach ($aData as $oNode){ ?>
			<tr class="<?=$sClass?>">
				<td width="<?=TIER_WIDTH?>"><?=$oNode->tierName?></td>
				<td width="<?=NAME_WIDTH?>"><?=$oNode->machineName?></td>
				<td width="<?=ID_WIDTH?>"><i><?=$oNode->machineOSType?></i></td>
				<td width="<?=ID_WIDTH?>"><?=$oNode->machineId?></td>
				<td width="<?=NAME_WIDTH?>"><?=$oNode->name?></td>
				<td width="<?=ID_WIDTH?>"><?=$oNode->id?></td>
				<td width="<?=AGENT_WIDTH?>"><?=cAppdynUtil::extract_agent_version($oNode->machineAgentVersion)?></td>
				<td width="<?=AGENT_WIDTH?>"><?=cAppdynUtil::extract_agent_version($oNode->appAgentVersion)?></td>
				<td width="<?=AGENT_WIDTH?>"><?=$oNode->agentType?></td>
			</tr><?php
		}
		?>
			<tr class="<?=$sClass?>"><td colspan="9">&nbsp;</td></tr>
		<?php
	
		if (cDebug::is_debugging() && !$bShown){
			$bShown = true;
			cDebug::vardump($aNodes[0],true);
		}
	}
?>
</table>

<?php
//####################################################################
cRender::button("Show All Agents", "allagents.php");	
cRender::html_footer();
?>
