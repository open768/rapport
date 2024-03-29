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

$sMetricType = cHeader::get(cRenderQS::METRIC_TYPE_QS);
switch($sMetricType){
	case cADMetricPaths::METRIC_TYPE_RUMCALLS:
	case cADMetricPaths::METRIC_TYPE_RUMRESPONSE:
		$sOtherTitle = "Application Activity";
		$sOtherUrl = cHttp::build_url(cCommon::filename(), cRenderQS::METRIC_TYPE_QS, cADMetricPaths::METRIC_TYPE_ACTIVITY);

		$sTitle1 = "Web Browser Page Requests";
		$sMetric1 = cADWebRumMetricPaths::CallsPerMin();
		$sTitle2 = "Web Browser Page Response";
		$sMetric2 = cADWebRumMetricPaths::ResponseTimes();
		$sTitle3 = "Pages With Javascript Errors";
		$sMetric3 = cADWebRumMetricPaths::JavaScriptErrors();
		
		$sBaseUrl = "$home/pages/rum/apprum.php";
		break;
	case cADMetricPaths::METRIC_TYPE_RESPONSE_TIMES:
	case cADMetricPaths::METRIC_TYPE_ACTIVITY:
	default:
		$sOtherTitle = "Web Browser Activity";
		$sOtherUrl = cHttp::build_url(cCommon::filename(), cRenderQS::METRIC_TYPE_QS, cADMetricPaths::METRIC_TYPE_RUMCALLS);
		$sTitle1 = "Application Activity";
		$sMetric1 = cADAppMetricPaths::appCallsPerMin();
		$sTitle2 = "Application Response Times";
		$sMetric2 = cADAppMetricPaths::appResponseTimes();
		$sTitle3 = "Application Errors";
		$sMetric3 = cADAppMetricPaths::appErrorsPerMin();
		$sBaseUrl = "$home/pages/app/tiers.php";
		break;
}

//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("All Applications - $sTitle1");
cRender::force_login();
cChart::do_header();
?><script src="<?=$jsWidgets?>/allapps.js"></script><?php

//###################################################################
//# CARD at top of page
cRenderCards::card_start();
	cRenderCards::body_start();
		cRender::add_filter_box("div[type=admenus]",cRenderQS::APP_QS,".mdl-card");
	cRenderCards::body_end();
	cRenderCards::action_start();
		cADCommon::button(cADControllerUI::apps_home());
		cRender::button($sOtherTitle,$sOtherUrl);
		$sUrl = cHttp::build_url(cCommon::filename(),cRenderQS::METRIC_TYPE_QS,$sMetricType);
		if (!cRender::is_list_mode()){
			$sUrl.= "&".cRenderQS::LIST_MODE_QS;
			cRender::button("list mode", $sUrl);
		}else			
			cRender::button("chart mode", $sUrl);
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
//# CARD that load all applicaions
?>
	<div 
		id='allapps' type='adWidget'  widget_type="allapps"
		<?=cRenderQS::HOME_QS?>='<?=$home?>' <?=cRenderQS::LIST_MODE_QS?>='<?=cRender::is_list_mode()?>' baseUrl = '<?=$sBaseUrl?>'
		title1='<?=$sTitle1?>' title2='<?=$sTitle2?>' title3='<?=$sTitle3?>'
		metric1='<?=$sMetric1?>' metric2='<?=$sMetric2?>' metric3='<?=$sMetric3?>'>
			please wait...
	</div>
	<script>
		function init_widget(){
			$("#allapps").adallapps(); //need to work with RUM too
		}
		
		$( init_widget);
	</script>
<?php


cRenderHtml::footer();
?>
