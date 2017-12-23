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
cRender::html_header("App Activity");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();

//####################################################################
//get passed in values
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);


$title= "$app&gt;Availability";
cRender::show_time_options($title); 
cRenderMenus::show_apps_menu("Availability", "appavail.php");

//####################################################################
//retrieve tiers
$oResponse =cAppdyn::GET_Tiers($app);

// work through each tier
?><h2>Availability for <?=$app?></h2><?php

$aMetrics = [];
foreach ( $oResponse as $oItem){
	$tier = $oItem->name;
	$tid= $oItem->id;

	$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>cRender::button_code($tier, cRender::getTierLinkUrl($app,$aid,$tier,$tid))];	
	$aMetrics[] = [cChart::LABEL=>"'$tier': Server availability",cChart::METRIC=>cAppDynMetric::InfrastructureMachineAvailability($tier)];
	$aMetrics[] = [cChart::LABEL=>"'$tier': infrastructure availability",cChart::METRIC=>cAppDynMetric::InfrastructureAgentAvailability($tier)];
}
$sClass = cRender::getRowClass();
cChart::metrics_table($app,$aMetrics,3,$sClass,null,cRender::CHART_WIDTH_LETTERBOX/2);
	
cChart::do_footer();

cRender::html_footer();
?>
