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


const COLUMNS=6;
error_reporting(E_ALL);

//display the results
$app = cHeader::get(cRender::APP_QS);
$fromtier = cHeader::get(cRender::FROM_TIER_QS);
$tid = cHeader::get(cRender::TIER_ID_QS);
$totier = cHeader::get(cRender::TO_TIER_QS);
$gsAppQS = cRender::get_base_app_qs();
$gsTierQS = cRender::build_tier_qs($gsAppQS, $fromtier, $tid);


//####################################################################
cRender::html_header("External tier calls");
cRender::force_login();
cChart::do_header();

//####################################################################
$title =  "$app&gt;$fromtier&gt; to tier $totier";		
cRender::show_time_options($title); 
cRenderMenus::show_tier_functions($fromtier,$tid);
cRender::button("back to ($fromtier) external tiers", cHttp::build_url("tierextgraph.php", $gsTierQS));
?>
<h2>Tier activity details<h2>
<h3>from (<?=$fromtier?>) to (<?=$totier?>)</h3>
<p>
<?php
	$aMetrics=[];
	$sMetricUrl=cAppDynMetric::tierExtCallsPerMin($fromtier, $totier);
	$aMetrics[] = [cChart::LABEL=>"Calls per min from ($fromtier) to ($totier)", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cAppDynMetric::tierExtResponseTimes($fromtier, $totier);
	$aMetrics[] = [cChart::LABEL=>"Response Times in ms from ($fromtier) to ($totier)", cChart::METRIC=>$sMetricUrl];
	cChart::metrics_table($app,$aMetrics,1,cRender::getRowClass());

//####################################################################
//################ CHART
cChart::do_footer();

cRender::html_footer();
?>