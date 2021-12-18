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

function count_nodes($paData){
	$iCount = 0;
	foreach ($paData as $aNodes)
		$iCount += count($aNodes);
		
	return $iCount;
}

//***********************************************************************
function render_tier_agents($paNodes){
	global $oApp;
	$aTierNodes = cADAnalysis::group_nodes_by_tier($paNodes);
	
	//----list all tiers--------------------------------------------------------
	cRenderCards::card_start("Tiers");
	cRenderCards::body_start();
		echo "<div style='column-count:4'>";
		$sFirstCh = "";
		foreach ($aTierNodes as $sTier=>$aNodes){
			if ($sFirstCh !== strtolower($sTier[0])) {
				echo "<p><font size='+2'><b>".strtoupper($sTier[0])."</b></font><br>";
				$sFirstCh = strtolower($sTier[0]);
			}
			echo "<a href='#$sTier'>$sTier</a><br>";
			
		}		
		echo "</div>";
	cRenderCards::body_end();
	cRenderCards::card_end();
	
	//-------------------------------------------------------------------------
	foreach ($aTierNodes as $sTier=>$aNodes){
		$oTier = cRenderObjs::make_tier_obj($oApp, $sTier, $aNodes[0]->tierId);
		
		cRenderCards::card_start("<a name='$sTier'>&nbsp;</a>");
		cRenderCards::body_start();
			if (cRender::is_list_mode()){
				echo "<div style='column-count:4'>";
				foreach ($aNodes as $oNode)
					echo $oNode->machineName."<br>";
				echo "</div>";
			}else{
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
									cADCommon::button(cADControllerUI::machineDetails($oNode->machineId), $oNode->machineName)
								?><td><?=$oNode->agentType?></td>
								<td align="right"><nobr><?php
									$sNodeUrl = cHttp::build_url("../tier/tierinfrstats.php", $sTierQS);
									cRender::button($oNode->name,cHttp::build_url($sNodeUrl,cRender::NODE_QS,$oNode->name));
									cADCommon::button(cADControllerUI::nodeAgent($oApp, $oNode->id),"Go");
								?></nobr></td>
								<td><?=($oNode->ipAddresses?$oNode->ipAddresses->ipAddresses[0]:"")?></td>
								<td><?=($oNode->machineAgentPresent?cADUtil::extract_agent_version($oNode->machineAgentVersion):"none")?></td>
								<td><?=($oNode->appAgentPresent?cADUtil::extract_agent_version($oNode->appAgentVersion):"none")?></td>
							</tr><?php
						}
				?></table></div><?php
			}
		cRenderCards::body_end();
		cRenderCards::action_start();
			cRenderMenus::show_tier_functions($oTier);
		cRenderCards::action_end();
		cRenderCards::card_end();
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
						<?=cADCommon::button(cADControllerUI::machineDetails($iMachineID), $sMachine)?>
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
							cADCommon::button(cADControllerUI::nodeAgent($oApp, $oNode->id),"Go");
							?></td>
						<td><?=($oNode->ipAddresses?$oNode->ipAddresses->ipAddresses[0]:"")?></td>
						<td><?=($oNode->machineAgentPresent?cADUtil::extract_agent_version($oNode->machineAgentVersion):"none")?></td>
						<td><?=($oNode->appAgentPresent?cADUtil::extract_agent_version($oNode->appAgentVersion):"none")?></td>
					</tr><?php
				}
			}
		?>
	</table>	
	<?php
}

//***********************************************************************
function render_agent_counts($psCaption, $paCounts){
	$aKeys = array_keys($paCounts);
	if (count($aKeys) == 0)
		echo "no agents found";
	else{
		asort($aKeys);
		?><table class="maintable" border="1" cellspacing="0">
			<tr class="tableheader">
				<td></td><?php
				foreach ($aKeys as $sKey)
					echo "<th>$sKey</th>";
			?></tr><tr>
				<td width="200" align="right"><?=$psCaption?> agent count</td><?php
				foreach ($aKeys as $sKey){
					$iCount = $paCounts[$sKey];
					echo "<td width='100' align='middle'>$iCount</td>";
				}
			?></tr>
		</table><?php
	}
		
		
}

//####################################################################
//#
//####################################################################
cRenderHtml::header("Application Nodes");
cRender::force_login();
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//####################################################################
$oApp = cRenderObjs::get_current_app();
$psAggType = 	cHeader::get(cRender::GROUP_TYPE_QS);
$sAppQS = cRenderQS::get_base_app_QS($oApp);
$sShowBaseUrl = cHttp::build_url(cCommon::filename(),$sAppQS);
$aMetrics = cADInfraMetric::getInfrastructureMetricTypes();
if ($psAggType == null) $psAggType = cRender::GROUP_TYPE_NODE;

//####################################################################
$aNodes = $oApp->GET_Nodes();
$iNodes = count_nodes($aNodes);	

//####################################################################
cRenderCards::card_start();
cRenderCards::body_start();
	if ($iNodes==0)
		cCommon::messagebox("no Agents found");
	else{
		echo "There are $iNodes Nodes in total";
		$oCounts = cADAnalysis::analyse_agent_versions($aNodes);
		$aMacCounts = $oCounts->machineAgents;
		render_agent_counts("machine", $aMacCounts);
		$aAppCounts = $oCounts->appAgents;
		render_agent_counts("App", $aAppCounts);
	}
cRenderCards::body_end();
cRenderCards::action_start();
	cRenderMenus::show_app_agent_menu();
	cRenderMenus::show_apps_menu("Show Agents for...");
	
	//********************************************************************
	cADCommon::button(cADControllerUI::nodes($oApp), "All nodes");
	if ($psAggType===cRender::GROUP_TYPE_NODE){
		$sUrl = cHttp::build_url($sShowBaseUrl, cRender::GROUP_TYPE_QS, cRender::GROUP_TYPE_TIER);
		cRender::button("Group by Tier",$sUrl);
	}else{
		$sUrl = cHttp::build_url($sShowBaseUrl, cRender::GROUP_TYPE_QS, cRender::GROUP_TYPE_NODE);
		cRender::button("Group by Node",$sUrl);
		
		$sUrl = cHttp::build_url($sShowBaseUrl,cRender::GROUP_TYPE_QS,$psAggType);
		if (!cRender::is_list_mode()){
				$sUrl.= "&".cRender::LIST_MODE_QS;
				cRender::button("list mode", $sUrl);
		}else			
				cRender::button("detail mode", $sUrl);
	}

cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
if ($iNodes > 0){
	switch($psAggType){
		case cRender::GROUP_TYPE_TIER:
			render_tier_agents($aNodes);
			break;
		case cRender::GROUP_TYPE_NODE:
			uasort($aNodes, "pr__sort_nodes");
			render_node_agents($aNodes);
			break;
		default:
			cCommon::errorbox("unknown groupp mode");
	}
}

cRenderHtml::footer();
?>
