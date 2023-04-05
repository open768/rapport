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

//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();

cRenderHtml::header("Events $oApp->name");
cRender::force_login();

//####################################################################


//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

$oTimes = cRender::get_times();

function sort_events($a,$b){
if ($a->eventTime == $b->eventTime) {
        return 0;
    }
    return ($a->eventTime < $b->eventTime) ? -1 : 1;	
}


$aEvents = $oApp->GET_Events($oTimes);
if (count($aEvents) == 0){
	cRenderCards::card_start();
	cRenderCards::body_start();
		cRenderMenus::show_app_change_menu("Events");
		cCommon::messagebox("No Events found");
		cRender::button("health rules", cHttp::build_url("healthrules.php", cRenderQS::APP_QS, $oApp->name));
	cRenderCards::body_end();
	cRenderCards::card_end();
		
	
	cRenderHtml::footer();
	exit;
}

uasort($aEvents,"sort_events");
$oAnalysedEvents = cADAnalysis::analyse_events($aEvents);
$aPolicyTypes = $oAnalysedEvents->types;
$aAnalysedEvents = $oAnalysedEvents->analysis;


//####################################################################

cRenderCards::card_start("Summary");
	cRenderCards::body_start();
		?><table class="maintable" border="1" cellspacing="0">
			<thead><tr>
				<th width="250">Health Rule</th><?php
				foreach ($aPolicyTypes as $sType=>$i){
					$sType = str_replace("_", " ", $sType);
					echo "<th align='middle'>$sType</th>";
				}
			?></tr></thead>
			<tbody><?php
				foreach ($aAnalysedEvents as $sKey=>$oItem){
					?><tr>
						<td width="250" align="right"><?=$sKey?></td><?php
						foreach ($aPolicyTypes as $sType=>$i){
							echo "<TD align='middle'>";
							if (array_key_exists($sType, $oItem->typeCount)) 
								echo $oItem->typeCount[$sType];
							echo "</TD>";
						}
					?></tr><?php
				}
		?></tbody>
		</table><?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cADCommon::button(cADControllerUI::events($oApp), "Events");
		cADCommon::button(cADControllerUI::app_health_rules($oApp), "Health Rules");
		cADCommon::button(cADControllerUI::app_health_policies($oApp), "Health Policies");
		cRender::button("health rules", cHttp::build_url("healthrules.php", cRenderQS::APP_QS, $oApp->name));
		cRenderMenus::show_app_change_menu("Events");
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
$aCorrelated = cADRestUI::GET_correlatedEvents($aEvents);
$aAnalysedEvents = cADAnalysis::analyse_CorrelatedEvents($aEvents, $aCorrelated);

$oAnalysedActions = cADAnalysis::analyse_CorrelatedEventActions($aAnalysedEvents);
//cDebug::vardump($oAnalysed);
$aActionTypes = $oAnalysedActions->types;
$aAnalysedActions = $oAnalysedActions->analysis;

cRenderCards::card_start("Action Summary");
	cRenderCards::body_start();
		?><table class="maintable" border="1" cellspacing="0">
			<thead><tr>
				<th width="250">Policies\Actions</th><?php
				foreach ($aActionTypes as $sType=>$i){
					$sType = str_replace("_", " ", $sType);
					echo "<th align='middle'>$sType</th>";
				}
			?></tr></thead>
			<tbody><?php
				foreach ($aAnalysedActions as $sKey=>$oItem){
					?><tr>
						<td width="250" align="right"><?=$sKey?></td><?php
						foreach ($aActionTypes as $sType=>$i){
							echo "<TD align='middle'>";
							if (array_key_exists($sType, $oItem->typeCount)) 
								echo $oItem->typeCount[$sType];
							echo "</TD>";
						}
					?></tr><?php
				}
		?></tbody>
		</table><?php
	cRenderCards::body_end();
cRenderCards::card_end();


//####################################################################
cRenderCards::card_start("Details");
cRenderCards::body_start();
?>
<div class="note">click on table heading to sort</div>
<table class="maintable" border="1" cellspacing="0" cellpadding="2" id="tblEvents"><?php
	//cDebug::vardump($aEvents);
	?><thead><tr>
			<th>Severity</th>
			<th>DateStamp</th>
			<th>Business Transaction</th>
			<th>Policy</th>
			<th>Type</th>
			<th>action</th>
			<th>Link</th>
	</tr></thead>
	<tbody><?php
		//cDebug::vardump($aAnalysedEvents);
		foreach ($aAnalysedEvents as $oEvent){
			switch ($oEvent->severity){
				case "WARN":
					$sBgColor="yellow";
					break;
				case "ERROR":
					$sBgColor="red";
					break;
				default:
					$sBgColor="white";
			}
			?>
			<tr>
				<td bgcolor="<?=$sBgColor?>"><?=$oEvent->severity?></td>
				<td>
					<nobr><?=date(cCommon::ENGLISH_DATE_FORMAT,(integer) ($oEvent->eventTime/1000))?></nobr>
				</td>
				<td><?=$oEvent->bt?></td>
				<td><?=$oEvent->policy?></td>
				<td><?=$oEvent->type?></td>
				<td><?php
					if($oEvent->action) cRenderW3::tag($oEvent->action->type,$oEvent->action->summary);
				?></td>
				<td><?=cADCommon::button($oEvent->deepLinkUrl,"")?></td>
			</tr>
			<?php	
		}
	?></tbody>
</table>
	<script>
		$( 
			function(){ 
				$("#tblEvents").tablesorter();
			} 
		);
	</script>
<?php
cRenderCards::body_end();
cRenderCards::card_end();
cRenderHtml::footer();
?>
