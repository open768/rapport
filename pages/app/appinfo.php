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



set_time_limit(200); 

//####################################################################
cRenderHtml::header("Information Points");
cRender::force_login();
cChart::do_header();

//####################################################################
//get passed in values
$oApp = cRenderObjs::get_current_app();

$title= "$oApp->name&gt;Information Points";

cRenderMenus::show_apps_menu("Information Points for", "appinfo.php");


//####################################################################
//retrieve info points
$oTimes = cRender::get_times();
$aInfoPoints = $oApp->GET_InfoPoints($oTimes);
cDebug::vardump($aInfoPoints, true);
if (count($aInfoPoints) == 0)
	cCommon::messagebox("No information points found");
else{
	foreach ($aInfoPoints as $oInfoPoint){
		?><div class="<?=cRender::getRowClass()?>"><?php
			$aMetrics=[];
			
			$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>$oInfoPoint->name, cChart::WIDTH=>180];
			$sMetricUrl = cADMetric::infoPointCallsPerMin($oInfoPoint->name);
			$aMetrics[] = [cChart::LABEL=>"Calls", cChart::METRIC=>$sMetricUrl];
			$sMetricUrl = cADMetric::infoPointResponseTimes($oInfoPoint->name);
			$aMetrics[] = [cChart::LABEL=>"Response", cChart::METRIC=>$sMetricUrl];
			$sMetricUrl = cADMetric::infoPointErrorsPerMin($oInfoPoint->name);
			$aMetrics[] = [cChart::LABEL=>"Errors", cChart::METRIC=>$sMetricUrl];
			cChart::metrics_table($oApp, $aMetrics,4,cRender::getRowClass());

		?></div><?php
	}
}

//####################################################################
cChart::do_footer();

cRenderHtml::footer();
?>
