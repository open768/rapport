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
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################

require_once("$phpinc/appdynamics/appdynamics.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


cRender::html_header("Application Nodes");
cRender::force_login();
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$psAggType = 	cHeader::get(cRender::GROUP_TYPE_QS);
if ($psAggType == null) $psAggType = cRender::GROUP_TYPE_NODE;
$sAppQS = cRender::get_base_app_QS();
$sShowBaseUrl = cHttp::build_url("appnodes.php",$sAppQS);
$aMetrics = cRender::getInfrastructureMetricTypes();

//####################################################################
cRender::show_top_banner("Agents for $app"); 
cRender::show_apps_menu("Agents", "appnodes.php");
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
$oCred = cRender::get_appd_credentials();
$sDetailBaseUrl =  cHttp::build_url("allnodedetail.php",$sAppQS);

if ($oCred->restricted_login == null){ 
	?><select id="nodeMenu">
		<option selected disabled>Show for all Servers...</option>
		<?php
			foreach ($aMetrics as $sMetricType){
				$oMetric = cRender::getInfrastructureMetric($app,null,$sMetricType);
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
<?php
}
cRender::appdButton(cAppDynControllerUI::nodes($aid), "All nodes");

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
			$TierID = $oNode->tierId;
			if (!array_key_exists((string)$TierID, $aTiers)) $aTiers[(string)$TierID] = [];
			$aTiers[(string)$TierID][] = $oNode;
		}
		
	return $aTiers;
}

function count_nodes($paData){
	$iCount = 0;
	foreach ($paData as $aNodes)
		$iCount += count($aNodes);
		
	return $iCount;
}

//####################################################################
$aResponse = cAppDyn::GET_AppNodes($aid);
cdebug::vardump($aResponse);
$iNodes = count_nodes($aResponse);
	
if ($iNodes==0){
	?>
		<div class="maintable"><h2>No agents found</h2></div>
	<?php
}else{
	if ($psAggType == cRender::GROUP_TYPE_TIER) 
		$aResponse= group_by_tier($aResponse);
	uasort($aResponse, "pr__sort_nodes");
?>
	<p>
	<h2>There are <?=$iNodes;?> agents in total in (<?=$app?>)</h2>
	<p>
	<table class="maintable" border="1" cellspacing="0" cellpadding="2">
		<tr>
			<th>Machine</th>
			<th>Tier</th>
			<th>Agent Type</th>
			<th width="200">Node</th>
			<th>IP Address</th>
			<th width="200">Machine Agent Version</th>
			<th width="200">App Agent Version</th>
		</tr>
		<?php
			foreach ($aResponse as $aNodes){
				$iRowSpan = count($aNodes) +1;
				$class=cRender::getRowClass();
				?><tr class='$class'><?php
					if ($psAggType== cRender::GROUP_TYPE_NODE){
						$sMachine = $aNodes[0]->machineName;
						$iMachineID = $aNodes[0]->machineId;
						?><td rowspan="<?=$iRowSpan?>"><nobr>
							<?=cRender::appdButton(cAppDynControllerUI::machineDetails($iMachineID), $sMachine)?> (<?=$iMachineID?>)
						</nobr></td><?php
					}else{
						?><td></td><?php
					}
					
					if ($psAggType== cRender::GROUP_TYPE_TIER){
						$sTid = $aNodes[0]->tierId;
						$sTier = $aNodes[0]->tierName;
						$sQuery = cHttp::build_qs($sAppQS,cRender::TIER_QS,$sTier);
						$sQuery = cHttp::build_qs($sQuery,cRender::TIER_ID_QS,$sTid);
						?><td rowspan="<?=$iRowSpan?>"><nobr>
							<?=cRender::button($sTier, cHttp::build_url("tierinfrstats.php",$sQuery))?>
						</nobr></td><?php
					}
				?></tr><?php
				
				sort ($aNodes);
				foreach ($aNodes as $oNode){
					$sMachine = $oNode->machineName;
					$iMachineID = $oNode->machineId;
					$sTier = $oNode->tierName;
					$sTierQS = cHttp::build_qs($sAppQS, cRender::TIER_QS,$sTier);
					$sTierQS = cHttp::build_qs($sTierQS, cRender::TIER_ID_QS,$oNode->tierId);
					
					?>
						<tr class="<?=$class?>">
							<td><nobr><?php
								if ($psAggType !== cRender::GROUP_TYPE_NODE){
									cRender::appdButton(cAppDynControllerUI::machineDetails($iMachineID), $sMachine);
									echo " ($iMachineID)";
								}
								if ($psAggType !== cRender::GROUP_TYPE_TIER) cRender::button($sTier, cHttp::build_url("tierinfrstats.php",$sTierQS));
							?></nobr></td>
							<td><?=$oNode->agentType?></td>
							<td><?php
								$sNodeUrl = cHttp::build_url("tierinfrstats.php", $sTierQS);
								cRender::button($oNode->name,cHttp::build_url($sNodeUrl,cRender::NODE_QS,$oNode->name));
								cRender::appdButton(cAppDynControllerUI::nodeAgent($aid, $oNode->id), $oNode->id);
							?></td>
							<td><?=($oNode->ipAddresses?$oNode->ipAddresses->ipAddresses[0]:"")?></td>
							<td><?=($oNode->machineAgentPresent?$oNode->machineAgentVersion:"none")?></td>
							<td><?=($oNode->appAgentPresent?$oNode->appAgentVersion:"none")?></td>
						</tr>
						<?php
				}
			}
		?>
	</table>
<?php
}
cRender::html_footer();
?>
