<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED**************************************************************************/

//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";

$sMetricType = cHeader::get(cRender::METRIC_TYPE_QS);
switch($sMetricType){
	case cADMetric::METRIC_TYPE_RUMCALLS:
	case cADMetric::METRIC_TYPE_RUMRESPONSE:
		$sOtherTitle = "Application Activity";
		$sOtherUrl = cHttp::build_url("all.php", cRender::METRIC_TYPE_QS, cADMetric::METRIC_TYPE_ACTIVITY);

		$sTitle1 = "Web Browser Page Requests";
		$sMetric1 = cADWebRumMetric::CallsPerMin();
		$sTitle2 = "Web Browser Page Response";
		$sMetric2 = cADWebRumMetric::ResponseTimes();
		$sTitle3 = "Pages With Javascript Errors";
		$sMetric3 = cADWebRumMetric::JavaScriptErrors();
		
		$sBaseUrl = "$home/pages/rum/apprum.php";
		break;
	case cADMetric::METRIC_TYPE_RESPONSE_TIMES:
	case cADMetric::METRIC_TYPE_ACTIVITY:
	default:
		$sOtherTitle = "Web Browser Activity";
		$sOtherUrl = cHttp::build_url("all.php", cRender::METRIC_TYPE_QS, cADMetric::METRIC_TYPE_RUMCALLS);
		$sTitle1 = "Application Activity";
		$sMetric1 = cADMetric::appCallsPerMin();
		$sTitle2 = "Application Response Times";
		$sMetric2 = cADMetric::appResponseTimes();
		$sTitle3 = "Application Errors";
		$sMetric3 = cADMetric::appErrorsPerMin();
		$sBaseUrl = "$home/pages/app/tiers.php";
		break;
}

//####################################################################
cRenderHtml::header("All Applications - $sTitle1");
cRender::force_login();

//####################################################################
cRenderCards::card_start();
	cRenderCards::body_start();
		cRender::add_filter_box("div[type=admenus]","appname",".mdl-card");
	cRenderCards::body_end();
	cRenderCards::action_start();
		cADCommon::button(cADControllerUI::apps_home());
		cRender::button($sOtherTitle,$sOtherUrl);
		$sUrl = cHttp::build_url("all.php",cRender::METRIC_TYPE_QS,$sMetricType);
		if (!cRender::is_list_mode()){
			$sUrl.= "&".cRender::LIST_MODE_QS;
			cRender::button("list mode", $sUrl);
		}else			
			cRender::button("chart mode", $sUrl);


	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
//this should be done asynchronously
$aResponse = cADApp::GET_Applications();
if ( count($aResponse) == 0)
	cCommon::messagebox("Nothing found");
else{
	if (cRender::is_list_mode()){
		cRenderCards::card_start();
			cRenderCards::body_start();
				$sLastCh = "";
				echo "There are ".count($aResponse)." applications<p>";
				echo '<DIV style="column-count:3">';
					foreach ( $aResponse as $oApp){
						$sApp = $oApp->name;
						$sCh = strtoupper($sApp[0]);
						if ($sCh !== $sLastCh){
							echo "<h3>$sCh</h3>";
							$sLastCh = $sCh;
						}
						$sUrl = cHttp::build_url($sBaseUrl, cRenderQS::get_base_app_QS($oApp));
						echo "<a href=\"$sUrl\">$sApp</a><br>";
					}
				echo '</DIV>';
			cRenderCards::body_end();
		cRenderCards::card_end();
	}
	else{
		
		cChart::do_header();
		cChart::$hideGroupIfNoData = true;
		
		cDebug::write( count($aResponse). " applications found");
		//display the results
		foreach ( $aResponse as $oApp){
			if (cFilter::isAppFilteredOut($oApp)) continue;
			$sUrl = cHttp::build_url($sBaseUrl, cRenderQS::get_base_app_QS($oApp));
			$aMetrics = [
				[cChart::LABEL=>$sTitle1, cChart::METRIC=>$sMetric1, cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"detail for $oApp->name", ],
				[cChart::LABEL=>$sTitle2, cChart::METRIC=>$sMetric2],
				[cChart::LABEL=>$sTitle3, cChart::METRIC=>$sMetric3]
			];
			
			cRenderCards::card_start();
				cRenderCards::body_start();
					cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
				cRenderCards::body_end();
				cRenderCards::action_start();
					cRenderMenus::show_app_functions($oApp);
				cRenderCards::action_end();
			cRenderCards::card_end();
			cDebug::flush();
			
			if (cDebug::is_extra_debugging()) {
				cDebug::vardump($oApp);	
				break;	//DEBUG
			}
			cCommon::flushprint("");
		}
		cChart::do_footer();
	}
}
cRenderHtml::footer();
?>
