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
cRenderHtml::header("Service End Points");
cRender::force_login();
cChart::do_header();

//####################################################################
//get passed in values
$oApp = cRenderObjs::get_current_app();
$oTimes = cRender::get_times();

cRenderCards::card_start();
	cRenderCards::body_start();
		cRender::add_filter_box("span[tier]","tier",".mdl-card");
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRenderMenus::show_apps_menu("Show Service EndPoints for");
		cADCommon::button(cADControllerUI::serviceEndPoints($oApp,$oTimes));
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
//retrieve tiers
//********************************************************************
$aTiers = $oApp->GET_Tiers();

function pr__sort_endpoints($a,$b){
	return strcmp($a->name, $b->name);
}

foreach ($aTiers as $oTier){
	//TODO make this asynchronous as this will crash when there are hundreds of  tiers
	
	//****************************************************************************************
	$aEndPoints = $oTier->GET_ServiceEndPoints();
	cRenderCards::card_start("<span tier='$oTier->name'>$oTier->name</span>");
	cRenderCards::body_start();
	
	if (count($aEndPoints) == 0)
		cCommon::messagebox("no Service endpoints found for $oTier->name");
	else{
		
		uasort($aEndPoints, "pr__sort_endpoints");
		$sTierQS = cRenderQS::get_base_tier_QS($oTier);

		//****************************************************************************************
		?><p><?php
		cRenderMenus::show_tier_functions($oTier);
		$aHeaders = ["End Point","Activity","Response Times in ms","Errors per minute"];
		$aMetrics = [];
		foreach ($aEndPoints as $oEndPoint){
			$sUrl = cHttp::build_qs($sTierQS, cRenderQS::SERVICE_QS, $oEndPoint->name);
			$sUrl = cHttp::build_qs($sUrl, cRenderQS::SERVICE_ID_QS, $oEndPoint->id);
			$sUrl = cHttp::build_url("$home/pages/service/endpoint.php", $sUrl);

			$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>$oEndPoint->name, cChart::WIDTH=>150];
			$aMetrics[] = [cChart::LABEL=>"Calls", cChart::METRIC=>cADMetricPaths::endPointCallsPerMin($oTier->name, $oEndPoint->name), cChart::GO_URL=>$sUrl];
			$aMetrics[] = [cChart::LABEL=>"Response", cChart::METRIC=>cADMetricPaths::endPointResponseTimes($oTier->name, $oEndPoint->name)];
			$aMetrics[] = [cChart::LABEL=>"Errors", cChart::METRIC=>cADMetricPaths::endPointErrorsPerMin($oTier->name, $oEndPoint->name)];
		}
		$sClass = cRender::getRowClass();
		cChart::metrics_table($oApp,$aMetrics,4,$sClass,null,(cChart::CHART_WIDTH_LETTERBOX-150)/3, $aHeaders);
	}
	cRenderCards::body_end();
	cRenderCards::card_end();
}

//####################################################################
cChart::do_footer();

cRenderHtml::footer();
?>
