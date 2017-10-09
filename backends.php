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
cRender::html_header("Backends");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();
cChart::$width=cRender::CHART_WIDTH_LARGE/2;


//get passed in values
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);

$sAppQs = cRender::get_base_app_QS();


$title= "$app;Backends";

cRender::show_time_options($title); 
cRenderMenus::show_apps_menu("Remote Services", "backends.php");
cRender::appdButton(cAppDynControllerUI::remoteServices($aid));

//retrieve tiers
$oBackends =cAppdyn::GET_Backends($app);


// work through each tier
?>
<h2>Overall Statistics for <?=$app?>
<table class="maintable">
	<tr class="<?=cRender::getRowClass()?>">
		<td><?php
			$sMetric=cAppDynMetric::appCallsPerMin();
			cChart::add("Overall Calls per min", $sMetric, $app);	
		?></td>
		<td><?php
			$sMetric=cAppDynMetric::appResponseTimes();
			cChart::add("Overall Response Times", $sMetric, $app);	
		?></td>
	</tr>
</table>
<p>

<h2>Remote Services for <?=$app?></h2>
<table class='maintable'>
	<?php
	$sBackendURL = cHttp::build_url("backcalls.php",$sAppQs );
	foreach ( $oBackends as $oBackend){
		$sBackend = $oBackend->name;
		$sClass = cRender::getRowClass();
		?><tr class="<?=$sClass?>" ><td colspan="2"><?php
			cRender::button($sBackend, cHttp::build_url($sBackendURL, cRender::BACKEND_QS, $sBackend));
		?></td></tr>
		<tr class="<?=$sClass?>">
			<td><?php
				$sMetric=cAppDynMetric::backendCallsPerMin($sBackend);
				cChart::add("Calls per min ($sBackend)", $sMetric, $app);	
			?></td>
			<td><?php
				$sMetric=cAppDynMetric::backendResponseTimes($sBackend);
				cChart::add("Response Times ($sBackend)", $sMetric, $app);	
			?></td>
		</tr><?php
	}
	?>
</table>
<?php
cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

cRender::html_footer();
?>
