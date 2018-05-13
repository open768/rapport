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
$home = "../..";
$root=realpath($home);
$phpinc = realpath("$root/../phpinc");
$jsinc = "$home/../jsinc";

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


cRender::html_header("All agents");
cRender::force_login();


//####################################################################
cRender::show_top_banner("All Agents"); 

$moApps = cAppDynController::GET_Applications();

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

function get_app_node_data($poApp){
	$aTierData = [];
	
	cDebug::write($poApp->name . ":" . $poApp->id);
	$aResponse = $poApp->GET_Nodes();
	foreach ($aResponse as $aNodes)
		foreach ($aNodes as $oNode){
			$sTier = $oNode ->tierName;
			
			if (!array_key_exists($sTier, $aTierData))	$aTierData[$sTier] = new cAgentTotals();
			$aTierData[$sTier]->total ++;
			if ($oNode->machineAgentPresent) $aTierData[$sTier]->machine ++;
			if ($oNode->appAgentPresent) $aTierData[$sTier]->appserver++;
		}
	cDebug::vardump($aTierData);
	return $aTierData;
}

?>
<h2>All Agents</h2>
<?php
const BLANK_WIDTH=200;
const TIERCOL_WIDTH=300;
const TOTALCOL_WIDTH=150;

if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}

//####################################################################
?>
<table class="maintable">
	<?php
	$oGrandTotal = new cAgentTotals();
	
	foreach ($moApps as $oApp){
		$aAppData = get_app_node_data($oApp);
		$oAppTotals = new cAgentTotals();
				
		if (count($aAppData) == 0) continue;
		
		$sClass = cRender::getRowClass();
		?><tr class="<?=$sClass?>"><td colspan="5" align="left">
			<?php
				cRenderMenus::show_app_functions($oApp);
				cRenderMenus::show_app_agent_menu($oApp);
			?>
		</tr>
		<tr class="tableheader">
			<th width="<?=BLANK_WIDTH?>"></th>
			<th width="<?=TIERCOL_WIDTH?>">Tier</th>
			<th width="<?=TOTALCOL_WIDTH?>">total</th>
			<th width="<?=TOTALCOL_WIDTH?>">Machine agents</th>
			<th width="<?=TOTALCOL_WIDTH?>">App server agents</th>
		</tr>
		<?php
			foreach ($aAppData as $sTier=>$oTierCounts){
				$oAppTotals->add($oTierCounts);
				$oGrandTotal->add($oTierCounts);
				?><tr class="<?=$sClass?>">
					<th width="<?=BLANK_WIDTH?>"></th>
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
			?><tr class="<?=$sClass?>">
				<td width="<?=BLANK_WIDTH?>"></td>
				<td width="<?=TIERCOL_WIDTH?>" align="right"><b>Total for <?=cRender::show_name(cRender::NAME_APP,$oApp)?></b></td>
				<td width="<?=TOTALCOL_WIDTH?>" align="middle"><b><font color="blue"><?=$oAppTotals->total?></font></b></td>
				<td width="<?=TOTALCOL_WIDTH?>" align="middle"><b><font color="blue"><?=$oAppTotals->machine?></font></b></td>
				<td width="<?=TOTALCOL_WIDTH?>" align="middle"><b><font color="blue"><?=$oAppTotals->appserver?></font></b></td>
			</tr>
			<tr class="<?=$sClass?>" align="left"><td colspan="5">&nbsp;</td></tr>
		<?php
	}
	?><tr>
		<td></td>
		<td width="<?=TIERCOL_WIDTH?>" align="right"><b>Grand Totals</b></td>
		<td width="<?=TOTALCOL_WIDTH?>" align="middle"><b><font color="blue" size="+1"><?=$oGrandTotal->total?></font></b></td>
		<td width="<?=TOTALCOL_WIDTH?>" align="middle"><b><font color="blue" size="+1"><?=$oGrandTotal->machine?></font></b></td>
		<td width="<?=TOTALCOL_WIDTH?>" align="middle"><b><font color="blue" size="+1"><?=$oGrandTotal->appserver?></font></b></td>
	</tr>
</table><?php

cRender::button("Show All Agent Versions", "allagentversions.php");	
cRender::html_footer();
?>
