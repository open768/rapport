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
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################

require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/metrics.php");
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");


cRenderHtml::header("Application Nodes");
cRender::force_login();


$oApp = cRenderObjs::get_current_app();
$psAggType = 	cHeader::get(cRender::GROUP_TYPE_QS);
if ($psAggType == null) $psAggType = cRender::GROUP_TYPE_NODE;
$sAppQS = cRenderQS::get_base_app_QS($oApp);
$sShowBaseUrl = cHttp::build_url("appagents.php",$sAppQS);
$aMetrics = cAppDynInfraMetric::getInfrastructureMetricTypes();

//####################################################################
cRender::show_top_banner("Agents for $oApp->name"); 
cRenderMenus::show_app_agent_menu();
cRenderMenus::show_apps_menu("Show Agents for...", "appagents.php");
//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRenderHtml::footer();
	exit;
}
	//********************************************************************
?>
<select id="showMenu">
	<option selected disabled>Show...</option>
	<option <?=($psAggType == cRender::GROUP_TYPE_NODE?"selected disabled":"")?> value="<?=cHttp::build_url($sShowBaseUrl, cRender::GROUP_TYPE_QS, cRender::GROUP_TYPE_NODE)?>">Group by Node</option>
	<option <?=($psAggType == cRender::GROUP_TYPE_TIER?"selected disabled":"")?> value="<?=cHttp::build_url($sShowBaseUrl, cRender::GROUP_TYPE_QS, cRender::GROUP_TYPE_TIER)?>">Group by Tier</option>
</select>
	<script language="javascript">
	$(  
		function(){
			$("#showMenu").selectmenu({change:common_onListChange});  
		}  
	);
	</script>
	<?php
$oCred = cRenderObjs::get_appd_credentials();
$sDetailBaseUrl =  cHttp::build_url("appagentdetail.php",$sAppQS);

if ($oCred->restricted_login == null){ 
	?><select id="nodeMenu">
		<option selected disabled>Show for all Servers...</option>
		<?php
			foreach ($aMetrics as $sMetricType){
				$oMetric = cAppDynInfraMetric::getInfrastructureMetric($oApp->name,null,$sMetricType);
				$sDetailUrl = cHttp::build_url($sDetailBaseUrl, cRender::METRIC_TYPE_QS, $sMetricType);
				?><option value="<?="$sDetailUrl"?>"><?=$oMetric->short?></option><?php
			}
		?>
	</select>
	<script language="javascript">
	$(  
		function(){
			$("#nodeMenu").selectmenu({change:common_onListChange});  
		}  
	);
	</script>
	<p>
<?php
}
cRender::appdButton(cAppDynControllerUI::nodes($oApp), "All nodes");

//####################################################################

//####################################################################

function pr__sort_nodes($a,$b){
	global $psAggType;
	
	switch ($psAggType){
		case cRender::GROUP_TYPE_NODE;
			return strcmp($a[0]->machineName, $b[0]->machineName);
			break;
		case cRender::GROUP_TYPE_TIER:
			return strcmp($a[0]->tierName, $b[0]->tierName);
			break;
	}
}


function group_by_tier($paNodes){
	$aTiers = [];
	
	foreach ($paNodes as $aNodes)
		foreach ($aNodes as $oNode){
			$sTier = $oNode->tierName;
			if (!array_key_exists($sTier, $aTiers)) $aTiers[$sTier] = [];
			$aTiers[$sTier][] = $oNode;
		}
		
	return $aTiers;
}

function count_nodes($paData){
	$iCount = 0;
	foreach ($paData as $aNodes)
		$iCount += count($aNodes);
		
	return $iCount;
}

//***********************************************************************
function render_tier_agents($paNodes){
	global $oApp;
	$aTierNodes = group_by_tier($paNodes);
	foreach ($aTierNodes as $sTier=>$aNodes){
		$oTier = cRenderObjs::make_tier_obj($oApp, $sTier, $aNodes[0]->tierId);
		?><p><?php
		cRenderMenus::show_tier_functions($oTier);
		$sTierQS = cRenderQS::get_base_tier_QS($oTier);
		?><div class="<?=cRender::getRowClass()?>"><table class="maintable">
			<tr class="tableheader">
				<th width="250">Machine</th>
				<th width="120">Agent Type</th>
				<th width="180">Node</th>
				<th width="120">IP Address</th>
				<th width="120">Machine Agent Version</th>
				<th width="120">App Agent Version</th>
			</tr>
			<?php
				sort ($aNodes);
				foreach ($aNodes as $oNode){
					$sMachine = $oNode->machineName;
					$iMachineID = $oNode->machineId;
					$sTier = $oNode->tierName;
					
					?><tr class="<?=$sClass?>">
						<td><?php
							cRender::appdButton(cAppDynControllerUI::machineDetails($oNode->machineId), $oNode->machineName)
						?><td><?=$oNode->agentType?></td>
						<td align="right"><nobr><?php
							$sNodeUrl = cHttp::build_url("../tier/tierinfrstats.php", $sTierQS);
							cRender::button($oNode->name,cHttp::build_url($sNodeUrl,cRender::NODE_QS,$oNode->name));
							cRender::appdButton(cAppDynControllerUI::nodeAgent($oApp, $oNode->id),"Go");
						?></nobr></td>
						<td><?=($oNode->ipAddresses?$oNode->ipAddresses->ipAddresses[0]:"")?></td>
						<td><?=($oNode->machineAgentPresent?cAppdynUtil::extract_agent_version($oNode->machineAgentVersion):"none")?></td>
						<td><?=($oNode->appAgentPresent?cAppdynUtil::extract_agent_version($oNode->appAgentVersion):"none")?></td>
					</tr><?php
				}
			?>
		</table></div>
	<?php
	}
}

//***********************************************************************
function render_node_agents($paData){
	global $sAppQS, $oApp;
	?>
	<table class="maintable">
		<tr class="tableheader">
			<th width="250">Machine</th>
			<th width="150">Tier</th>
			<th width="120">Agent Type</th>
			<th width="180">Node</th>
			<th width="120">IP Address</th>
			<th width="120">Machine Agent Version</th>
			<th width="120">App Agent Version</th>
		</tr>
		<?php
			foreach ($paData as $aNodes){
				$iRowSpan = count($aNodes) +1;
				$sClass=cRender::getRowClass();
				?><tr class='$sClass'><?php
					$sMachine = $aNodes[0]->machineName;
					$iMachineID = $aNodes[0]->machineId;
					?><td rowspan="<?=$iRowSpan?>"><nobr>
						<?=cRender::appdButton(cAppDynControllerUI::machineDetails($iMachineID), $sMachine)?>
					</nobr></td><?php
				?></tr><?php
				
				sort ($aNodes);
				foreach ($aNodes as $oNode){
					$sMachine = $oNode->machineName;
					$iMachineID = $oNode->machineId;
					$sTier = $oNode->tierName;
					$sTierQS = cHttp::build_qs($sAppQS, cRender::TIER_QS,$sTier);
					$sTierQS = cHttp::build_qs($sTierQS, cRender::TIER_ID_QS,$oNode->tierId);
					
					?><tr class="<?=$sClass?>">
						<td><nobr><?php
							cRender::button($sTier, cHttp::build_url("../tier/tierinfrstats.php",$sTierQS));
						?></nobr></td>
						<td><?=$oNode->agentType?></td>
						<td><?php
							$sNodeUrl = cHttp::build_url("../tier/tierinfrstats.php", $sTierQS);
							cRender::button($oNode->name,cHttp::build_url($sNodeUrl,cRender::NODE_QS,$oNode->name));
							cRender::appdButton(cAppDynControllerUI::nodeAgent($oApp, $oNode->id),"Go");
						?></td>
						<td><?=($oNode->ipAddresses?$oNode->ipAddresses->ipAddresses[0]:"")?></td>
						<td><?=($oNode->machineAgentPresent?cAppdynUtil::extract_agent_version($oNode->machineAgentVersion):"none")?></td>
						<td><?=($oNode->appAgentPresent?cAppdynUtil::extract_agent_version($oNode->appAgentVersion):"none")?></td>
					</tr><?php
				}
			}
		?>
	</table>	
	<?php
}

//***********************************************************************
$aData = $oApp->GET_Nodes();
$iNodes = count_nodes($aData);	
?>
	<h2>There are <?=$iNodes;?> agents in total in <?=cRender::show_name(cRender::NAME_APP,$oApp)?></h2>
<?php
if ($iNodes==0)
	cRender::messagebox("no Agents found");
else
	switch($psAggType){
		case cRender::GROUP_TYPE_TIER:
			render_tier_agents($aData);
			break;
		case cRender::GROUP_TYPE_NODE:
			uasort($aData, "pr__sort_nodes");
			render_node_agents($aData);
			break;
		default:
			cRender::errorbox("unknown groupp mode");
	}
cRenderHtml::footer();
?>
