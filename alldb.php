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
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/http.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


//####################################################################
cRender::html_header("All Applications - Databases");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();
cChart::$width=cRender::CHART_WIDTH_LARGE -200;


//####################################################################
cRender::show_time_options( "All Applications - Time in Databases"); 
cRender::appdButton(cAppDynControllerUI::databases());

//####################################################################
$oResponse = cAppDyn::GET_Databases();
if (count($oResponse) == 0){
	cRender::messagebox("No Monitored Databases found");
}else{
	//tables are needed here as each chart is for a different application
	?><h2>Databases</h2><?php	
	//display the results
	$aMetrics = [];
	
	foreach ( $oResponse as $oDB){
		$class=cRender::getRowClass();
		$sDB=$oDB->name;
		$sMetric = cAppDynMetric::databaseTimeSpent($sDB);

		$sButton = cRender::button_code($sDB, "db.php?".cRender::DB_QS."=$sDB", false);
		$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>$sButton];
		$aMetrics[] = [cChart::LABEL => $sDB, cChart::METRIC=>$sMetric, cChart::APP=>cAppDynCore::DATABASE_APPLICATION];
	}
	cChart::metrics_table(cAppDApp::$db_app, $aMetrics, 2, cRender::getRowClass());
}
cChart::do_footer();

cRender::html_footer();
?>
