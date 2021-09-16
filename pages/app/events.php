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
	cRender::errorbox("function not supported for Demo");
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
	cRender::errorbox("No Events found");
	cRenderHtml::footer();
	exit;
}

uasort($aEvents,"sort_events");
$oAnalysed = cADUtil::analyse_events($aEvents);
$aTypes = $oAnalysed->types;
$aAnalysed = $oAnalysed->analysis;


//####################################################################

cRenderCards::card_start("Summary");
	cRenderCards::body_start();
		?><table class="maintable" border="1" cellspacing="0">
			<thead><tr>
				<th width="250">Health Rule</th><?php
				foreach ($aTypes as $sType=>$i){
					$sType = str_replace("_", " ", $sType);
					echo "<th align='middle'>$sType</th>";
				}
			?></tr></thead>
			<tbody><?php
				foreach ($aAnalysed as $sKey=>$oItem){
					?><tr>
						<td width="250" align="right"><?=$sKey?></td><?php
						foreach ($aTypes as $sType=>$i){
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
		cRender::appdButton(cADControllerUI::events($oApp), "Events");
		cRender::appdButton(cADControllerUI::app_health_rules($oApp), "Health Rules");
		cRender::appdButton(cADControllerUI::app_health_policies($oApp), "Health Policies");
		cRender::button("health rules", cHttp::build_url("healthrules.php", cRender::APP_QS, $oApp->name));
		cRenderMenus::show_apps_menu("Events", "events.php");
	cRenderCards::action_end();
cRenderCards::card_end();

$aCorrelated = cAD_RestUI::GET_correlatedEvents($aEvents);
$aAnalysedEvents = cADUtil::analyse_CorrelatedEvents($aEvents, $aCorrelated);


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
				<td><?=($oEvent->action==null?"":"X")?></td>
				<td><?=cRender::appdButton($oEvent->deepLinkUrl,"")?></td>
			</tr>
			<?php	
		}
	?></tbody>
</table>
	<script language="javascript">
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
