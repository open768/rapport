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
cRender::html_header("Information Points");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();

//####################################################################
//get passed in values
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);


$title= "$app&gt;Information Points";
cRender::show_time_options($title); 
cRenderMenus::show_apps_menu("Information Points for", "appinfo.php");

//####################################################################
//retrieve tiers
$oTimes = cRender::get_times();
$aInfoPoints = cAppdyn::GET_AppInfoPoints($app, $oTimes);
cDebug::vardump($aInfoPoints, true);
if (count($aInfoPoints) == 0)
	cRender::messagebox("No information points found");
else{
	foreach ($aInfoPoints as $oInfoPoint){
		?><div class="<?=cRender::getRowClass()?>"><?php
			$aMetrics=[];
			
			$sMetricUrl = cAppdynMetric::infoPointCallsPerMin($oInfoPoint->name);
			$aMetrics[] = ["Calls", $sMetricUrl];
			$sMetricUrl = cAppdynMetric::infoPointResponseTimes($oInfoPoint->name);
			$aMetrics[] = ["Response", $sMetricUrl];
			$sMetricUrl = cAppdynMetric::infoPointErrorsPerMin($oInfoPoint->name);
			$aMetrics[] = ["Errors", $sMetricUrl];
			cRender::render_metrics_table($aid, $aMetrics,3,cRender::getRowClass());

		?></div><?php
	}
}

//####################################################################
cChart::do_footer();

cRender::html_footer();
?>
