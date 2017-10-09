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
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");

//-----------------------------------------------
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);

cRender::html_header("Events $app");
cRender::force_login();

//####################################################################
cRender::show_time_options("Events");
cRenderMenus::show_apps_menu("Events", "events.php", $app);
cRender::appdButton(cAppDynControllerUI::events($aid));

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

$aEvents = cAppDyn::GET_AppEvents($app, $oTimes);
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
	cRender::html_footer();
?>
