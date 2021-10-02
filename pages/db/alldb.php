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

cADCommon::button(cADControllerUI::databases());

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

//####################################################################
$oResponse = cADController::GET_Databases();
if (count($oResponse) == 0){
	cCommon::messagebox("No Monitored Databases found");
}else{
	//tables are needed here as each chart is for a different application
	?><h2>Databases</h2><?php	
	//display the results
	$aMetrics = [];
	
	foreach ( $oResponse as $oDB){
		$class=cRender::getRowClass();
		$sDB=$oDB->name;
		$sMetric = cADMetric::databaseTimeSpent($sDB);

		$sButton = cRender::button_code($sDB, "db.php?".cRender::DB_QS."=$sDB", false);
		$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>$sButton];
		$aMetrics[] = [cChart::LABEL => $sDB, cChart::METRIC=>$sMetric, cChart::APP=>cADCore::DATABASE_APPLICATION];
	}
	cChart::metrics_table(cADApp::$db_app, $aMetrics, 2, cRender::getRowClass());
}
cChart::do_footer();

cRenderHtml::footer();
?>
