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
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


$sMetricType = cHeader::get(cRender::METRIC_TYPE_QS);
switch($sMetricType){
	case cRender::METRIC_TYPE_RUMCALLS:
	case cRender::METRIC_TYPE_RUMRESPONSE:
		$sTitle1 = "Web Browser Page Requests";
		$sMetric1 = cAppDynMetric::webrumCallsPerMin();
		$sTitle2 = "Web Browser Page Response";
		$sMetric2 = cAppDynMetric::webrumResponseTimes();
		break;
	case cRender::METRIC_TYPE_RESPONSE_TIMES:
	case cRender::METRIC_TYPE_ACTIVITY:
	default:
		$sTitle1 = "Application Activity";
		$sMetric1 = cAppDynMetric::appCallsPerMin();
		$sTitle2 = "Application Response Times";
		$sMetric2 = cAppDynMetric::appResponseTimes();
		break;
}

//####################################################################
cRender::html_header("All Applications - $sTitle1");
cRender::force_login();
?>
	<script type="text/javascript" src="js/widgets/chart.js"></script>
<?php
cChart::do_header();

//####################################################################
cRender::show_time_options( "All Applications - $sTitle1"); 		

//####################################################################
$aResponse = cAppDyn::GET_Applications();
if ( count($aResponse) == 0)
	cRender::messagebox("Nothing found");
else{
	?><div ><?php	
		//display the results
		foreach ( $aResponse as $oApp){
			if (cFilter::isAppFilteredOut($oApp->name)) continue;
			$sClass = cRender::getRowClass();			
			$aMetrics = [
				[cChart::LABEL=>$sTitle1, cChart::METRIC=>$sMetric1],
				[cChart::LABEL=>$sTitle2, cChart::METRIC=>$sMetric2]
			];
			cRenderMenus::show_app_functions($oApp);
			cChart::metrics_table($oApp, $aMetrics,2,$sClass);
		}
	?></div><?php
}
?>

<?php
cChart::do_footer();
cRender::html_footer();
?>
