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
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";



//####################################################################
cRenderHtml::header("All Applications - Databases");
cRender::force_login();
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE -200;


//####################################################################


//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

//####################################################################
$oResponse = cADDB::GET_Databases();

cRenderCards::card_start("Databases");
cRenderCards::body_start();
if (count($oResponse) == 0) cCommon::messagebox("No Monitored Databases found");
cRenderCards::body_end();
cRenderCards::action_start();
	cADCommon::button(cADControllerUI::databases());
	cRender::button("custom metrics","metrics.php");
cRenderCards::action_end();
cRenderCards::card_end();


if (count($oResponse) > 0){
	cRenderCards::card_start();
	cRenderCards::body_start();
	
	//tables are needed here as each chart is for a different application
	$aMetrics = [];
	
	foreach ( $oResponse as $oDB){
		$class=cRender::getRowClass();
		$sDB=$oDB->name;
		$sMetric = cADMetricPaths::databaseTimeSpent($sDB);

		$sButton = cRender::button_code($sDB, "db.php?".cRender::DB_QS."=$sDB", false);
		$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>$sButton];
		$aMetrics[] = [cChart::LABEL=>$sDB, cChart::METRIC=>$sMetric, cChart::APP=>cADCore::DATABASE_APPLICATION];
	}
	cChart::metrics_table(cADDB::$db_app, $aMetrics, 2, cRender::getRowClass());
	cRenderCards::body_end();
	cRenderCards::card_end();
}
	

cChart::do_footer();
cRenderHtml::footer();
?>
