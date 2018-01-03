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

//get passed in values
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$oApp = cRender::get_current_app();

$sAppQs = cRender::get_base_app_QS();


$title= "$app;Backends";

cRender::show_time_options($title); 
cRenderMenus::show_apps_menu("Remote Services", "backends.php");
cRender::appdButton(cAppDynControllerUI::remoteServices($aid));

	//********************************************************************
	if (cAppdyn::is_demo()){
		cRender::errorbox("function not support ed for Demo");
		exit;
	}
	//********************************************************************

//retrieve tiers
$oBackends =cAppdyn::GET_Backends($app);


// work through each tier
?><h2>Overall Statistics for <?=$app?><?php
$aMetrics = [];
$aMetrics[] = [cChart::LABEL=>"Overall Calls per min", cChart::METRIC=>cAppDynMetric::appCallsPerMin()];
$aMetrics[] = [cChart::LABEL=>"Overall Response Times", cChart::METRIC=>cAppDynMetric::appResponseTimes()];
cChart::metrics_table($oApp, $aMetrics,2,cRender::getRowClass());

?><h2>Remote Services for <?=$app?></h2>
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
