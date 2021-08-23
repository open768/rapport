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
cRender::show_time_options("Events");
cRender::appdButton(cAppDynControllerUI::events($oApp));
cRender::button("health rules", cHttp::build_url("healthrules.php", cRender::APP_QS, $oApp->name));
cRenderMenus::show_apps_menu("Events", "events.php", $oApp->name);

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

$oTimes = cRender::get_times();
cCommon::flushprint("");

function sort_events($a,$b){
if ($a->eventTime == $b->eventTime) {
        return 0;
    }
    return ($a->eventTime < $b->eventTime) ? -1 : 1;	
}

function get_BT_from_event($poEvent){
	$sBT = "transaction unknown";
	
	foreach ($poEvent->affectedEntities as $oItem){
		if ($oItem->entityType == "BUSINESS_TRANSACTION"){
			$sBT = $oItem->name;
			break;
		}
	}
	return $sBT;
}

$aEvents = $oApp->GET_Events($oTimes);
uasort($aEvents,"sort_events");
//cDebug::vardump($aEvents,true);

//####################################################################

	?>
	<table class="maintable" border="1" cellspacing="0" cellpadding="2"><?php
		if (count($aEvents) == 0) 
			echo "<tr><td><h2>No Events found</h2></td></tr>";
		else{
			?>
				<tr>
					<th>Severity</th>
					<th>DateStamp</th>
					<th>Type</th>
					<th>Summary</th>
					<th>Link</th>
				</tr>
			<?php
			//cDebug::vardump($aEvents, true);
			foreach ($aEvents as $oEvent){
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
					<td><nobr><?=date(cCommon::ENGLISH_DATE_FORMAT,(integer) ($oEvent->eventTime/1000))?></nobr></td>
					<td><?=get_BT_from_event($oEvent)?></td>
					<td><?=$oEvent->summary?></td>
					<td><?=cRender::appdButton(cAppDynControllerUI::event_detail($oEvent->id))?></td>
				</tr>
				<?php	
			}
		}
	?></table>
<?php
	cRenderHtml::footer();
?>
