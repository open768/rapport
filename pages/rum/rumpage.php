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



//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();
$rum_page = cHeader::get(cRenderQS::RUM_PAGE_QS);
$rum_page_id = cHeader::get(cRenderQS::RUM_PAGE_ID_QS);
$rum_type = cHeader::get(cRenderQS::RUM_TYPE_QS);
$gsAppQS = cRenderQS::get_base_app_QS($oApp);

//####################################################################
$title ="$oApp->name&gtWeb Real User Monitoring Details&gt;$rum_page";
cRenderHtml::header("Web browser - Real user monitoring - $rum_page");

cRender::force_login();
cChart::do_header();

cRenderMenus::show_app_functions($oApp);
cRender::button("Back to page requests", "rumstats.php?$gsAppQS");
cADCommon::button(cADControllerUI::webrum_detail($oApp, $rum_page_id));

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

//####################################################################

?><H2>Real User Monitoring Details for (<?=$rum_page?>)</h2><?php
$aMetrics = [];
$sMetricUrl=cADWebRumMetric::PageCallsPerMin($rum_type, $rum_page);
$aMetrics[] = [cChart::LABEL=>"Page requests: $rum_page", cChart::METRIC=>$sMetricUrl];
$sMetricUrl=cADWebRumMetric::PageResponseTimes($rum_type, $rum_page);
$aMetrics[] = [cChart::LABEL=>"Page Response times: $rum_page", cChart::METRIC=>$sMetricUrl];
$sMetricUrl=cADWebRumMetric::PageTCPTime($rum_type, $rum_page);
$aMetrics[] = [cChart::LABEL=>"Page connection time", cChart::METRIC=>$sMetricUrl];
$sMetricUrl=cADWebRumMetric::PageServerTime($rum_type, $rum_page);
$aMetrics[] = [cChart::LABEL=>"Page Server time", cChart::METRIC=>$sMetricUrl];
$sMetricUrl=cADWebRumMetric::PageFirstByte($rum_type, $rum_page);
$aMetrics[] = [cChart::LABEL=>"Page first byte time", cChart::METRIC=>$sMetricUrl];
$sMetricUrl=cADWebRumMetric::PageJavaScriptErrors($rum_type, $rum_page);
$aMetrics[] = [cChart::LABEL=>"Page Views with Javascript errors", cChart::METRIC=>$sMetricUrl];
cChart::metrics_table($oApp, $aMetrics,2,cRender::getRowClass());			

cChart::do_footer();
cRenderHtml::footer();
?>
