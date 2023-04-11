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
//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";


set_time_limit(200); 

//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("App Activity");
cRender::force_login();
cChart::do_header();

//####################################################################
$oApp = cRenderObjs::get_current_app();


// work through each tier
cRenderCards::card_start("Availability for $oApp->name");
	cRenderCards::action_start();
		cRenderMenus::show_app_change_menu("Availability");
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
//retrieve tiers
$oResponse =$oApp->GET_Tiers();

$aMetrics = [];
foreach ( $oResponse as $oTier){
	$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>cRender::button_code($oTier->name, cRender::getTierLinkUrl($oApp->name,$oApp->id,$oTier->name,$oTier->id))];	
	$aMetrics[] = [cChart::LABEL=>"'$oTier->name': Server availability",cChart::METRIC=>cADInfraMetric::InfrastructureMachineAvailability($oTier->name)];
	$aMetrics[] = [cChart::LABEL=>"'$oTier->name': infrastructure availability",cChart::METRIC=>cADInfraMetric::InfrastructureAgentAvailability($oTier->name)];
}
$sClass = cRender::getRowClass();
cRenderCards::card_start("Details");
	cRenderCards::body_start();
		cChart::metrics_table($oApp,$aMetrics,3,$sClass,null,cChart::CHART_WIDTH_LETTERBOX/2);
	cRenderCards::body_end();
cRenderCards::card_end();
	
cChart::do_footer();

cRenderHtml::footer();
?>
