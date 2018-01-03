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
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


//####################################################################
cRender::html_header("All Tiers");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();

//####################################################################
cRender::show_time_options( "All Tiers"); 
	
	//********************************************************************
	if (cAppdyn::is_demo()){
		cRender::errorbox("function not support ed for Demo");
		exit;
	}
	//********************************************************************
	

$aApps = cAppDyn::GET_Applications();
if (count($aApps) == 0) cRender::errorbox("No Applications found");

//####################################################################
foreach ( $aApps as $oApp){
	if (cFilter::isAppFilteredOut($oApp->name)) continue;
	$sAppQS = cRender::build_app_qs($oApp->name, $oApp->id);
	$sClass = cRender::getRowClass();
	?><DIV><?php
		cRenderMenus::show_app_functions($oApp);
		$aTiers =cAppdyn::GET_Tiers($oApp->name);
		$aMetrics = [];
		foreach ($aTiers as $oTier){ 
			if (cFilter::isTierFilteredOut($oTier->name)) continue;
			
			$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>$oTier->name];
			$aMetrics[] = [cChart::LABEL=>"calls: $oTier->name", cChart::METRIC=>cAppDynMetric::tierCallsPerMin($oTier->name)];
			$aMetrics[] = [cChart::LABEL=>"Response: $oTier->name", cChart::METRIC=>cAppDynMetric::tierResponseTimes($oTier->name)];
			$sTierQs = cRender::build_tier_qs($sAppQS, $oTier->name, $oTier->id );
			$aMetrics[] = [	cChart::TYPE=>cChart::LABEL, cChart::LABEL=>cRender::button_code("Go", "tier.php?$sTierQs") ];
		}
		cChart::metrics_table($oApp,$aMetrics,4,$sClass,null,cRender::CHART_WIDTH_LETTERBOX/3);
	?></div><?php
}
cChart::do_footer();

cRender::html_footer();
?>
