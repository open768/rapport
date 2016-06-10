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


cRender::html_header("All agents");
cRender::force_login();


//####################################################################
cRender::show_top_banner("All Agents"); 

$moApps = cAppDyn::GET_Applications();

class cAgentTotals {
	public $total=0;
	public $machine=0;
	public $appserver=0;
	
	public function add($poAgentTotals){
		$this->total += $poAgentTotals->total;
		$this->machine += $poAgentTotals->machine;
		$this->appserver += $poAgentTotals->appserver;
	}
}

function get_app_node_data($psAid){
	$aTierData = [];
	
	$aResponse = cAppDyn::GET_AppNodes($psAid);
	foreach ($aResponse as $aNodes)
		foreach ($aNodes as $oNode){
			$sTier = $oNode ->tierName;
			
			if (!array_key_exists($sTier, $aTierData))	$aTierData[$sTier] = new cAgentTotals();
			$aTierData[$sTier]->total ++;
			if ($oNode->machineAgentPresent) $aTierData[$sTier]->machine ++;
			if ($oNode->appAgentPresent) $aTierData[$sTier]->appserver++;
		}
	
	return $aTierData;
}

function get_all_app_node_data(){
	global $moApps;
	
	$aData = [];
	
	foreach ($moApps as $oApp){
		$sApp=$oApp->name;
		$sAid=$oApp->id;
		
		cCommon::flushprint();
		$aAppData = get_app_node_data($sAid);
		$aData [$sApp] = $aAppData;
	}
	
	return $aData;
}


?>
<h2>All Agents</h2>
<div id="progress"><?php	
	$aAllData = get_all_app_node_data();
?></div>
<?php
if (!cDebug::is_debugging()){ 
	?><script language="javascript">
		function clearProgresStatus(){
			$("#progress").empty();
		}
		$(clearProgresStatus);
	</script><?php 
}
const APPCOL_WIDTH=200;
const TIERCOL_WIDTH=300;
const TOTALCOL_WIDTH=150;

//####################################################################
?>
<table class="maintable">
	<tr>
		<th width="<?=APPCOL_WIDTH?>">Application</th>
		<th>Agents</th>
	</tr>
	<?php
	$oGrandTotal = new cAgentTotals();
	
	foreach ($moApps as $oApp){
		$sApp=$oApp->name;
		$sAid=$oApp->id;
		$sAppUrl = "appnodes.php?".cRender::APP_QS."=$sApp&".cRender::APP_ID_QS."=$sAid";
		$aAppData = $aAllData[$sApp];
		$oAppTotals = new cAgentTotals();
		
		
		if (count($aAppData) == 0) continue;
		
		
		?><tr class="<?=cRender::getRowClass()?>">
			<td width="<?=APPCOL_WIDTH?>">
				<?=cRender::Button($sApp, $sAppUrl)?>
			</td>
			<td><table width="100%">
				<tr>
					<th width="<?=TIERCOL_WIDTH?>">Tier</th>
					<th width="<?=TOTALCOL_WIDTH?>">total</th>
					<th width="<?=TOTALCOL_WIDTH?>">Machine agents</th>
					<th width="<?=TOTALCOL_WIDTH?>">App server agents</th>
				</tr>
			<?php
				foreach ($aAppData as $sTier=>$oTierCounts){
					$oAppTotals->add($oTierCounts);
					$oGrandTotal->add($oTierCounts);
					?><tr>
						<td width="<?=TIERCOL_WIDTH?>"><?=$sTier?></td>
						<td width="<?=TOTALCOL_WIDTH?>" align="middle"><?=$oTierCounts->total?></td>
						<td width="<?=TOTALCOL_WIDTH?>" align="middle"><?=$oTierCounts->machine?></td>
						<?php
							if ($oTierCounts->machine != $oTierCounts->appserver){
								?><td width="<?=TOTALCOL_WIDTH?>"align="middle"><font color="red"><b><?=$oTierCounts->appserver?></b></font></td><?php
							}else{
								?><td width="<?=TOTALCOL_WIDTH?>"align="middle"><?=$oTierCounts->appserver?></td><?php
							}
						?>
					</tr><?php
				}
				?><tr>
					<td width="<?=TIERCOL_WIDTH?>" align="right">Total for <?=$sApp?> &gt; </td>
					<td width="<?=TOTALCOL_WIDTH?>" align="middle"><b><font color="blue"><?=$oAppTotals->total?></font></b></td>
					<td width="<?=TOTALCOL_WIDTH?>" align="middle"><b><font color="blue"><?=$oAppTotals->machine?></font></b></td>
					<td width="<?=TOTALCOL_WIDTH?>" align="middle"><b><font color="blue"><?=$oAppTotals->appserver?></font></b></td>
				</tr><?php
			?></table></td>
		</tr><?php
	}
	?><tr>
		<td width="<?=APPCOL_WIDTH?>" align="right">&nbsp;</td>
		<td>
			<table width="100%"><tr>
				<td width="<?=TIERCOL_WIDTH?>" align="right"><b>Grand Totals</b></td>
				<td width="<?=TOTALCOL_WIDTH?>" align="middle"><b><font color="blue" size="+1"><?=$oGrandTotal->total?></font></b></td>
				<td width="<?=TOTALCOL_WIDTH?>" align="middle"><b><font color="blue" size="+1"><?=$oGrandTotal->machine?></font></b></td>
				<td width="<?=TOTALCOL_WIDTH?>" align="middle"><b><font color="blue" size="+1"><?=$oGrandTotal->appserver?></font></b></td>
			</tr></table>
		</td>
	</tr>
</table><?php

	
cRender::html_footer();
?>
