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
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");

$SHOW_PROGRESS=false;
set_time_limit(200); 

//####################################################################
cRender::html_header("Service End Points");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();
cChart::$width = cRender::CHART_WIDTH_LETTERBOX/4;

//####################################################################
//get passed in values
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);


$title= "$app&gt;Service EndPoints";
cRender::show_time_options($title); 
cRender::show_apps_menu("Show Service EndPoints for", "appservice.php");
if (cFilter::isFiltered()){
	$sCleanAppQS = cRender::get_clean_base_app_QS();
	cRender::button("Clear Filter", "appservice.php?$sCleanAppQS");
}
//####################################################################
//retrieve tiers
$oTimes = cRender::get_times();
$aTiers = cAppdyn::GET_Tiers($app, $oTimes);


foreach ($aTiers as $oTier){
	if (cFilter::isTierFilteredOut($oTier->name)) continue;

	//****************************************************************************************
	$aEndPoints = cAppdyn::GET_TierServiceEndPoints($app, $oTier->name);
	if (count($aEndPoints) == 0){
		cRender::messagebox("no Service endpoints found for $oTier->name");
		continue;
	}
	
	//****************************************************************************************
	?><p><?php
	cRender::show_tier_functions($oTier->name, $oTier->id);
	?><table class="maintable">
		<tr>
			<th>End Point</th>
			<th>Activity</th>
			<th>Response Times in ms</th>
			<th>Errors per minute</th>
		</tr>
		<?php
			foreach ($aEndPoints as $oEndPoint){
				?><tr class="<?=cRender::getRowClass()?>">
					<td><?=$oEndPoint->name?></td>
					<td><?php	
						$sMetricUrl = cAppdynMetric::endPointCallsPerMin($oTier->name, $oEndPoint->name);
						cChart::add("Calls", $sMetricUrl, $app, cRender::CHART_HEIGHT_SMALL);
					?></td>
					<td><?php	
						$sMetricUrl = cAppdynMetric::endPointResponseTimes($oTier->name, $oEndPoint->name);
						cChart::add("Response", $sMetricUrl, $app, cRender::CHART_HEIGHT_SMALL);
					?></td>
					<td><?php	
						$sMetricUrl = cAppdynMetric::endPointErrorsPerMin($oTier->name, $oEndPoint->name);
						cChart::add("Errors", $sMetricUrl, $app, cRender::CHART_HEIGHT_SMALL);
					?></td>
				</tr><?php
			}
		?>
	</table><?php 
}

//####################################################################
cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

cRender::html_footer();
?>
