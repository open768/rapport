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
require_once("../../inc/root.php");
cRoot::set_root("../..");
require_once("$root/inc/common.php");
require_once("$root/inc/inc-charts.php");


//####################################################################
cRenderHtml::header("All Applications - Databases");
cRender::force_login();
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE -200;


//####################################################################
cRender::show_time_options( "All Applications - Time in Databases"); 
cRender::appdButton(cAppDynControllerUI::databases());

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

//####################################################################
$oResponse = cAppDynController::GET_Databases();
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

cRenderHtml::footer();
?>
