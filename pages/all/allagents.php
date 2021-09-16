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

cRenderHtml::header("All agents");
cRender::force_login();

//********************************************************************************
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


//********************************************************************************
const BLANK_WIDTH=200;
const TIERCOL_WIDTH=300;
const TOTALCOL_WIDTH=150;

$moApps = cADController::GET_Applications();
if (cAD::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRenderHtml::footer();
	exit;
}

//#############################################################
cRenderCards::card_start();
	cRenderCards::action_start();
		cRender::appdButton(cADControllerUI::agents());
		cRender::button("Show All Agent Versions", "allagentversions.php");	
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
$oGrandTotal = new cAgentTotals();

foreach ($moApps as $oApp){
	$aNodes = $oApp->GET_nodes();
	$aAppData = cADUtil::analyse_app_nodes($aNodes);
	$oAppTotals = new cAgentTotals();
	
	cRenderCards::card_start($oApp->name);
	cRenderCards::body_start();
		if (count($aAppData) == 0) continue;
		
		$sClass = cRender::getRowClass();
		?>
		<table>
			<tr class="tableheader">
				<th width="<?=TIERCOL_WIDTH?>">Tier</th>
				<th width="<?=TOTALCOL_WIDTH?>">Machine agents</th>
				<th width="<?=TOTALCOL_WIDTH?>">App server agents</th>
			</tr>
			<?php
			foreach ($aAppData as $sTier=>$oTierCounts){
				$oAppTotals->add($oTierCounts);
				$oGrandTotal->add($oTierCounts);
				?><tr class="<?=$sClass?>">
					<td width="<?=TIERCOL_WIDTH?>"><?=$sTier?></td>
					<td width="<?=TOTALCOL_WIDTH?>" align="middle"><?=$oTierCounts->machine?></td>
					<?php
						if ($oTierCounts->machine != $oTierCounts->appserver){
							?><td width="<?=TOTALCOL_WIDTH?>"align="middle">
								<font color="red"><b><?=$oTierCounts->appserver?></b></font>
							</td><?php
						}else{
							?><td width="<?=TOTALCOL_WIDTH?>"align="middle">
								<?=$oTierCounts->appserver?>
							</td><?php
						}
					?>
				</tr><?php
			} //foreach
			?><tr class="<?=$sClass?>">
				<td width="<?=TIERCOL_WIDTH?>" align="right"><b>Total for <?=cRender::show_name(cRender::NAME_APP,$oApp)?></b></td>
				<td width="<?=TOTALCOL_WIDTH?>" align="middle"><b><font color="blue"><?=$oAppTotals->machine?></font></b></td>
				<td width="<?=TOTALCOL_WIDTH?>" align="middle"><b><font color="blue"><?=$oAppTotals->appserver?></font></b></td>
			</tr>
			<tr class="<?=$sClass?>" align="left"><td colspan="5">&nbsp;</td></tr>
		</table>
		<?php
		cRenderCards::body_end();
		cRenderCards::action_start();
			cRenderMenus::show_app_functions($oApp);
			cRenderMenus::show_app_agent_menu($oApp);
		cRenderCards::action_end();
	cRenderCards::card_end();
}

//#############################################################
cRenderCards::card_start("Grand Totals");
	cRenderCards::body_start();
	?>
		Machine Agent: <?=$oGrandTotal->total?><br>
		App Agent: <?=$oGrandTotal->appserver?>
	<?php
	cRenderCards::body_end();
cRenderCards::card_end();

cRenderHtml::footer();
?>
