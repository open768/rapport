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
cChart::do_header();

//get passed in values
$oApp = cRender::get_current_app();
$sAppQs = cRender::get_base_app_QS();


$title= "$oApp->name;Backends";

cRender::show_time_options($title); 
cRenderMenus::show_apps_menu("Remote Services", "appbackends.php");
cRender::appdButton(cAppDynControllerUI::remoteServices($oApp->id));

//retrieve tiers
$oBackends =cAppdyn::GET_Backends($oApp->name);


// work through each tier
?><h2>Overall Statistics for <?=$oApp->name?><?php
$aMetrics = [];
$aMetrics[] = [cChart::LABEL=>"Overall Calls per min", cChart::METRIC=>cAppDynMetric::appCallsPerMin()];
$aMetrics[] = [cChart::LABEL=>"Overall Response Times", cChart::METRIC=>cAppDynMetric::appResponseTimes()];
cChart::metrics_table($oApp, $aMetrics,2,cRender::getRowClass());

?><h2>Remote Services for <?=$oApp->name?></h2>
	<?php
	$sBackendURL = cHttp::build_url("backcalls.php",$sAppQs );
	foreach ( $oBackends as $oBackend){
		$sBackend = $oBackend->name;
		$sClass = cRender::getRowClass();
		?><div class="<?=$sClass?>" ><?php
			cRender::button($sBackend, cHttp::build_url($sBackendURL, cRender::BACKEND_QS, $sBackend));
			$aMetrics = [];
			$aMetrics[] = [cChart::LABEL=>"Calls per min ($sBackend)", cChart::METRIC=>cAppDynMetric::backendCallsPerMin($sBackend)];
			$aMetrics[] = [cChart::LABEL=>"Response Times ($sBackend)", cChart::METRIC=>cAppDynMetric::backendResponseTimes($sBackend)];
			cChart::metrics_table($oApp, $aMetrics,2,$sClass);
		?></div><?php
	}
			
cChart::do_footer();

cRender::html_footer();
?>
