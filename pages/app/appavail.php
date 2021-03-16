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
require_once("../../inc/root.php");
cRoot::set_root("../..");
require_once("$root/inc/common.php");
require_once("$root/inc/inc-charts.php");

set_time_limit(200); 

//####################################################################
cRenderHtml::header("App Activity");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();

//####################################################################
//get passed in values

$oApp = cRenderObjs::get_current_app();


$title= "$oApp->name&gt;Availability";
cRender::show_time_options($title); 
cRenderMenus::show_apps_menu("Availability", "appavail.php");

//####################################################################
//retrieve tiers
$oResponse =$oApp->GET_Tiers();

// work through each tier
?><h2>Availability for <?=cRender::show_name(cRender::NAME_APP,$oApp)?></h2><?php


$aMetrics = [];
foreach ( $oResponse as $oTier){
	$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>cRender::button_code($oTier->name, cRender::getTierLinkUrl($oApp->name,$oApp->id,$oTier->name,$oTier->id))];	
	$aMetrics[] = [cChart::LABEL=>"'$oTier->name': Server availability",cChart::METRIC=>cAppDynMetric::InfrastructureMachineAvailability($oTier->name)];
	$aMetrics[] = [cChart::LABEL=>"'$oTier->name': infrastructure availability",cChart::METRIC=>cAppDynMetric::InfrastructureAgentAvailability($oTier->name)];
}
$sClass = cRender::getRowClass();
cChart::metrics_table($oApp,$aMetrics,3,$sClass,null,cChart::CHART_WIDTH_LETTERBOX/2);
	
cChart::do_footer();

cRenderHtml::footer();
?>
